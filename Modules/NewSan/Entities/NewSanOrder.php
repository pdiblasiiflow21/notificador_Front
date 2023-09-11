<?php

declare(strict_types=1);

namespace Modules\NewSan\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewSanOrder extends Model
{
    use HasFactory;

    protected $table = 'NewSan_orders';

    protected $primaryKey = 'api_id';

    public const NO_FINALIZADO = 0;

    public const FINALIZADO = 1;

    protected $fillable = [
        'api_id',
        'order_id',
        'shipment_id',
        'tracking_id',
        'state',
        'date',
        'finalized',
    ];
}
