<?php

declare(strict_types=1);

namespace Modules\NewSan\Repositories\V1;

use App\Repository\EloquentRepository;
use Illuminate\Support\Facades\Log;
use Modules\NewSan\Entities\NewSanOrder;

class NewSanOrderRepository extends EloquentRepository
{
    public function __construct()
    {
        parent::__construct(new NewSanOrder());
    }

    public function saveNewSanOrder(array $order)
    {
        $dataOrder = [
            'api_id'      => $order['id'],
            'order_id'    => $order['order_id'],
            'shipment_id' => $order['shipment_id'],
            'tracking_id' => $order['tracking_id'],
            'state'       => $order['state'],
            'date'        => $order['date'],
        ];

        $this->create($dataOrder);

        Log::channel('new_san_orders')->info("Se ha guardado una nueva orden en NewSan_orders. ID de la orden: {$order['id']}");
    }
}
