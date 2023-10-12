<?php

declare(strict_types=1);

namespace Modules\NewSan\Services\V1;

use App\Exceptions\Api\TokenVencidoException;
use App\Exports\NewSanOrderInformedExport;
use App\Service\V1\IflowApiService;
use App\Service\V1\NewSanApiService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Modules\NewSan\Entities\NewSanOrder;
use Modules\NewSan\Entities\NewSanOrderInformed;
use Modules\NewSan\Repositories\V1\NewSanNotificationLogRepository;
use Modules\NewSan\Repositories\V1\NewSanOrderInformedRepository;
use Modules\NewSan\Repositories\V1\NewSanOrderRepository;

class NewSanService
{
    private $iflowApiService;

    private $newSanApiService;

    private $newSanOrderRepository;

    private $newSanOrderInformedRepository;

    private $newSanNotificationLogRepository;

    private $successfulNotifications = 0;

    private $successfulFinalized = 0;

    private $notifiedArray = [];

    private $finalizedArray = [];

    public function __construct(
        IflowApiService $iflowApiService,
        NewSanApiService $newSanApiService,
        NewSanOrderRepository $newSanOrderRepository,
        NewSanOrderInformedRepository $newSanOrderInformedRepository,
        NewSanNotificationLogRepository $newSanNotificationLogRepository
    ) {
        $this->iflowApiService                 = $iflowApiService;
        $this->newSanApiService                = $newSanApiService;
        $this->newSanOrderRepository           = $newSanOrderRepository;
        $this->newSanOrderInformedRepository   = $newSanOrderInformedRepository;
        $this->newSanNotificationLogRepository = $newSanNotificationLogRepository;
    }

    public function notificationLogs(Request $request)
    {
        $paginatedData = $this->newSanNotificationLogRepository->notificationLogs($request);

        return [
            'data'        => $paginatedData->items(),
            'total'       => $paginatedData->total(),
            'perPage'     => $paginatedData->perPage(),
            'currentPage' => $paginatedData->currentPage(),
            'lastPage'    => $paginatedData->lastPage(),
        ];
    }

    public function exportNotificationLog(int $logId, array $columns)
    {
        $dateTimeNow = date('d-m-Y_H-i-s');
        $fileName    = 'NewSan_notificados_'.$logId.'_'.$dateTimeNow.'.csv';
        $export      = new NewSanOrderInformedExport($logId, $columns);

        // Puedo guardar el archivo que el cliente descarga, es una opcion
        // $filePath = 'exports/' . $fileName;
        // Excel::store($export, $filePath, 'local', \Maatwebsite\Excel\Excel::CSV);

        return Excel::download($export, $fileName, \Maatwebsite\Excel\Excel::CSV);
    }

    public function notifyOrders(Request $request)
    {
        try {
            $startTime = microtime(true);

            $this->processSellerOrders($request);
            $this->processNotFinalizedOrders();
            $this->processNotify();

            $endTime  = microtime(true);
            $duration = ($endTime - $startTime) * 1000;

            $this->newSanNotificationLogRepository->create([
                'message'       => 'Se notificaron '.$this->successfulNotifications.' orders a la api de NewSan. Los finalizados son: '.$this->successfulFinalized,
                'notified'      => json_encode($this->notifiedArray),
                'finalized'     => json_encode($this->finalizedArray),
                'response_time' => $duration,
            ]);

            return [
                'notifications' => $this->successfulNotifications,
                'finalized'     => $this->successfulFinalized,
            ];
        } catch (TokenVencidoException $tokenVencido) {
            throw $tokenVencido;
        } catch (\Throwable $th) {
            $res = $th;
        }
    }

    private function processSellerOrders(Request $request)
    {
        foreach ($this->iflowApiService->getSellerOrdersGenerator($request) as $order) {
            $newSanOrderData = [
                'api_id'      => $order['id'],
                'order_id'    => $order['order_id'],
                'shipment_id' => $order['shipment_id'],
                'tracking_id' => $order['tracking_id'],
                'state'       => $order['state'],
                'date'        => $order['date'],
            ];

            $this->newSanOrderRepository->updateOrCreate(
                [
                    'shipment_id' => $order['shipment_id'],
                ],
                $newSanOrderData
            );
        }
    }

    public function processNotFinalizedOrders()
    {
        $uninformedOrders = $this->newSanOrderRepository->getUnfinalizedOrders();

        $uninformedOrders->each(function (NewSanOrder $newSanOrder) {
            $newSanOrder = $newSanOrder->toArray();
            // Consulto el listado de estados de la orden en la api de Iflow
            $orderStatusArray = $this->iflowApiService->getStatusOrder($newSanOrder['tracking_id']);
            $lastStateArray   = end($orderStatusArray['results']['shippings'][0]['states']);
            // actualizo el state de NewSan_orders y/o lo marca como finalized(si esta en `Entregado` o `En proceso de devolucion`)
            $newSanOrder = $this->updateLastStateNewSanOrder($newSanOrder, $lastStateArray);
            // crea o actualiza un registro en NewSan_orders_informed
            $this->updateOrCreateNewSanOrderInformed($newSanOrder, $lastStateArray);
        });
    }

    /**
     * Actualiza el estado de un registro en NewSan_orders
     * Si el último estado informado por la api es `Entregado` o `En proceso de devolucion` marca el registro como finalized.
     */
    public function updateLastStateNewSanOrder(array $dataFromDDBB, array $lastStateArray): NewSanOrder
    {
        $lastStateName = $lastStateArray['state_name'];

        // Verifica si el último estado está entre los estados finalizados
        $isFinalized = in_array($lastStateName, NewSanOrder::STATES_WITH_FINALIZED_TRUE, true);

        // lo marco como finalized para que que se vuelva a tomar en el proceso
        $dataFromDDBB['finalized'] = $isFinalized;
        $dataFromDDBB['state']     = $lastStateName;

        $newSanOrder = $this->newSanOrderRepository->updateOrCreate(
            [
                'api_id' => $dataFromDDBB['api_id'],
            ],
            $dataFromDDBB
        );

        return $newSanOrder;
    }

    public function updateOrCreateNewSanOrderInformed(NewSanOrder $newSanOrder, array $lastStateArray): NewSanOrderInformed
    {
        $data = [
            'api_id'      => $newSanOrder->api_id,
            'order_id'    => $newSanOrder->order_id,
            'shipment_id' => $newSanOrder->shipment_id,
            'tracking_id' => $newSanOrder->tracking_id,
            'state_id'    => $lastStateArray['state_id'],
            'state_name'  => $lastStateArray['state_name'],
            'message'     => $lastStateArray['details'],
            'state_date'  => $lastStateArray['state_date'],
        ];

        $orderInformed = $this->newSanOrderInformedRepository->updateOrCreate(
            [
                'shipment_id' => $newSanOrder->shipment_id,
            ],
            $data
        );

        return $orderInformed;
    }

    /**
     * Notifica a la api de NewSan los registros de NewSan_orders_informed que no están marcados como finalizada.
     *
     * Este método recupera todas las órdenes que tienen el indicador "finalized" establecido en false
     * y las notifica a la API de NewSan mediante la función `notifyApiNewSan`. Si la API devuelve una
     * respuesta exitosa y el estado de la orden está en la lista de estados que deben marcarse como finalizados,
     * entonces se actualizará el indicador "finalized" del registro.
     *
     * @throws \Exception Puede lanzar una excepción si algo sale mal durante la notificación o actualización de las órdenes.
     * @return void
     */
    public function processNotify()
    {
        // voy a informar siempre que el flag finalized no sea true
        $uninformedOrders = $this->newSanOrderInformedRepository->getUnfinalizedOrders();

        $uninformedOrders->each(function (NewSanOrderInformed $orderInformed) {
            // Verifica si el estado actual es diferente del estado previamente notificado
            if ($orderInformed->state_name !== $orderInformed->last_notified_state) {
                $responseApi = $this->notifyApiNewSan($orderInformed);

                // con la respuesta del endpoint newsan voy a actualizar el flag finalized solo cuando sea Entregado o En proceso de devolucion
                if ($responseApi['code'] === 200) {
                    $this->successfulNotifications++;
                    $this->notifiedArray[] = $orderInformed->api_id;

                    // Actualiza el estado notificado
                    $orderInformed->last_notified_state = $orderInformed->state_name;
                    $orderInformed->save();

                    // una vez notificado a la api de NewSan lo marco como finalized (si corresponde) para que no se vuelva a tomar en el proceso
                    if (in_array($orderInformed->state_name, NewSanOrder::STATES_WITH_FINALIZED_TRUE, true)) {
                        $this->successfulFinalized++;
                        $orderInformed->markAsFinalized();
                        $this->finalizedArray[] = $orderInformed->api_id;
                    }
                }
            }
        });
    }

    /**
     * Notifica el estado actual de una orden a la API de NewSan.
     *
     * Esta función toma un modelo de NewSanOrderInformed y prepara los datos de la orden
     * para enviarlos a la API de NewSan mediante una solicitud POST. Los datos incluyen
     * información como el ID de la orden, el ID del envío, el ID de seguimiento,
     * el último estado y los detalles adicionales.
     *
     * @param  NewSanOrderInformed $orderInformed El modelo que contiene la información de la orden a notificar.
     * @throws \Exception                         Si la API devuelve un error o si falla la solicitud.
     * @return array                              Un array que contiene la respuesta de la API de NewSan.
     */
    public function notifyApiNewSan(NewSanOrderInformed $orderInformed): array
    {
        $orderData = [
            'IdOrder'    => $orderInformed->order_id,
            'IdShip'     => $orderInformed->shipment_id,
            'IdTrack'    => $orderInformed->tracking_id,
            'Last_state' => NewSanOrderInformed::HASHMAP_STATE_NAME_TO_STATE_NAME_UPPER[$orderInformed->state_name],
            'Details'    => $orderInformed->message,
            'State_date' => NewSanOrderInformed::transformDateTime($orderInformed->state_date),
        ];

        $apiResponse = $this->newSanApiService->postStatus($orderData);

        return $apiResponse;
    }
}
