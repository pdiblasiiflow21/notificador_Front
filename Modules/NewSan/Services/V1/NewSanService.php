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

            return $this->successfulNotifications;
        } catch (TokenVencidoException $tokenVencido) {
            throw $tokenVencido;
        } catch (\Throwable $th) {
            $res = $th;
        }
    }

    private function processSellerOrders(Request $request)
    {
        foreach ($this->iflowApiService->getSellerOrdersGenerator($request) as $order) {
            $this->handleSingleSellerOrder($order);
        }
    }

    private function handleSingleSellerOrder($order)
    {
        if ($this->isNewOrder($order['shipment_id'])) {
            $this->newSanOrderRepository->saveNewSanOrder($order);
        }
    }

    private function isNewOrder($shipmentId)
    {
        return $this->newSanOrderRepository->findAllBy('shipment_id', $shipmentId)->count() === 0;
    }

    private function processNotFinalizedOrders()
    {
        $ordersNotFinalized = $this->newSanOrderRepository->findAllBy('finalized', NewSanOrder::NO_FINALIZADO);

        if ($ordersNotFinalized->count() > 0) {
            foreach ($ordersNotFinalized as $order) {
                $this->handleNotFinalizedOrder($order);
            }
        }
    }

    private function handleNotFinalizedOrder($order)
    {
        // Consulto el estado de la orden en la api de Iflow
        $orderStatusObject = $this->iflowApiService->getStatusOrder($order['tracking_id']);

        $states = $orderStatusObject['results']['shippings'][0]['states'];
        $this->handleLastShippingState($order, $states);
    }

    private function handleLastShippingState($order, $states)
    {
        $lastState = end($states);
        $stateName = $lastState['state_name'];

        if ($stateName !== NewSanOrderInformed::ENTREGADO && (
            $this->isNewInformedOrder($order['shipment_id']) || (
                ! $this->isNewInformedOrder($order['shipment_id']) && ! $this->isOrderInformed($order['shipment_id'])
            )
        )
        ) {
            $this->newSanOrderInformedRepository->saveNewSanOrderInformed($order, $lastState);
            $apiResponse = $this->notifyExternalApi($order, $lastState);
            $this->updateInformedOrder($order, $apiResponse);
        }
    }

    private function isNewInformedOrder($shipmentId)
    {
        return $this->newSanOrderInformedRepository->findAllBy('shipment_id', $shipmentId)->count() === 0;
    }

    private function isOrderInformed($shipmentId)
    {
        $matchingOrders = $this->newSanOrderInformedRepository->findAllBy('shipment_id', $shipmentId);

        foreach ($matchingOrders as $order) {
            if ($order->informed === true) {
                return true;
            }
        }

        // Si no se encuentra ningún registro con 'informed' establecido en true, retorna false
        return false;
    }

    private function notifyExternalApi($order, $lastState)
    {
        $orderData = [
            'IdOrder'    => $order['order_id'],
            'IdShip'     => $order['shipment_id'],
            'IdTrack'    => $order['tracking_id'],
            'Last_state' => $lastState['state_name'],
            'Details'    => $lastState['details'],
            'State_date' => $lastState['state_date'],
        ];

        $apiResponse = $this->newSanApiService->postStatus($orderData);

        return $apiResponse;
    }

    private function updateInformedOrder($order, $apiResponse)
    {
        if (isset($apiResponse['code']) && $apiResponse['code'] === 200) {
            $orderInformedBBDD = $this->newSanOrderInformedRepository->findBy('tracking_id', $order['tracking_id']);
            $this->newSanOrderInformedRepository->update(['informed' => true], $orderInformedBBDD->api_id);
            $this->newSanOrderRepository->update(['finalized' => true], $orderInformedBBDD->api_id);
            $this->successfulNotifications++;
        }
        // TODO: ver que pasa si la api no devuelve la estructura esperada [code: 200, shortDescription: "OK", longDscription: "SUCCESS"]
    }
}
