<?php

declare(strict_types=1);

namespace Modules\NewSan\Services\V1;

use App\Exceptions\Api\TokenVencidoException;
use App\Service\V1\IflowApiService;
use App\Service\V1\NewSanApiService;
use Illuminate\Http\Request;
use Modules\NewSan\Entities\NewSanOrder;
use Modules\NewSan\Entities\NewSanOrderInformed;
use Modules\NewSan\Repositories\V1\NewSanOrderInformedRepository;
use Modules\NewSan\Repositories\V1\NewSanOrderRepository;

class NewSanService
{
    private $iflowApiService;

    private $newSanApiService;

    private $newSanOrderRepository;

    private $newSanOrderInformedRepository;

    private $successfulNotifications = 0;

    private $successfulFinalized = 0;

    public function __construct(
        IflowApiService $iflowApiService,
        NewSanApiService $newSanApiService,
        NewSanOrderRepository $newSanOrderRepository,
        NewSanOrderInformedRepository $newSanOrderInformedRepository
    ) {
        $this->iflowApiService               = $iflowApiService;
        $this->newSanApiService              = $newSanApiService;
        $this->newSanOrderRepository         = $newSanOrderRepository;
        $this->newSanOrderInformedRepository = $newSanOrderInformedRepository;
    }

    public function notifyOrders(Request $request)
    {
        try {
            $this->processSellerOrders($request);
            $this->processNotFinalizedOrders();
            $this->processNotify();

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

            NewSanOrder::updateOrCreate(
                [
                    'shipment_id' => $order['shipment_id'],
                ],
                $newSanOrderData
            );
        }
    }

    public function processNotFinalizedOrders()
    {
        $uninformedOrders = NewSanOrder::getUnfinalizedOrders();

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

        $dataFromDDBB['finalized'] = $isFinalized;
        $dataFromDDBB['state']     = $lastStateName;

        $newSanOrder = NewSanOrder::updateOrCreate(
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

        $orderInformed = NewSanOrderInformed::updateOrCreate(
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
        $uninformedOrders = NewSanOrderInformed::getUnfinalizedOrders();

        $uninformedOrders->each(function (NewSanOrderInformed $orderInformed) {
            $res = $this->notifyApiNewSan($orderInformed);

            // con la respuesta del endpoint newsan voy a actualizar el flag finalized solo cuando sea Entregado o En proceso de devolucion
            if ($res['code'] === 200) {
                $this->successfulNotifications++;
                if (in_array($orderInformed->state_name, NewSanOrder::STATES_WITH_FINALIZED_TRUE, true)) {
                    $this->successfulFinalized++;
                    $orderInformed->markAsFinalized();
                    $res = $orderInformed;
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
            'Last_state' => $orderInformed->state_name,
            'Details'    => $orderInformed->message,
            'State_date' => $orderInformed->state_date,
        ];

        $apiResponse = $this->newSanApiService->postStatus($orderData);

        return $apiResponse;
    }
}
