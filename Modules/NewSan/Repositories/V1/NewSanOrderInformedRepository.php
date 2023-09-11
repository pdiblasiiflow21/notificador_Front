<?php

declare(strict_types=1);

namespace Modules\NewSan\Repositories\V1;

use App\Repository\EloquentRepository;
use Illuminate\Support\Facades\Log;
use Modules\NewSan\Entities\NewSanOrderInformed;

class NewSanOrderInformedRepository extends EloquentRepository
{
    public function __construct()
    {
        parent::__construct(new NewSanOrderInformed());
    }

    public function saveNewSanOrderInformed($order, $lastState)
    {
        $dataOrderInformed = [
            'api_id'      => $order['api_id'],
            'order_id'    => $order['order_id'],
            'shipment_id' => $order['shipment_id'],
            'tracking_id' => $order['tracking_id'],
            'state_id'    => $lastState['state_id'],
            'state_name'  => $lastState['state_name'],
            'message'     => $lastState['details'],
            'state_date'  => $lastState['state_date'],
        ];

        $this->create($dataOrderInformed);

        Log::channel('new_san_orders_informed')->info("Se ha guardado una nueva orden en NewSan_orders_informed. ID de la orden: {$order['api_id']}");
    }
}
