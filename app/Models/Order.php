<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="Order",
 *      required={"id", "order_id", "shipment_id", "tracking_id", "state", "state_class", "items", "sender", "receiver", "value"},
 *      @OA\Property(
 *          property="id",
 *          type="integer",
 *          description="ID único de la orden",
 *          example=22540859
 *      ),
 *      @OA\Property(
 *          property="order_id",
 *          type="string",
 *          description="ID de la orden",
 *          example="97200000295765"
 *      ),
 *      @OA\Property(
 *          property="shipment_id",
 *          type="string",
 *          description="ID de envio",
 *          example="RRZ0000002108020"
 *      ),
 *      @OA\Property(
 *          property="tracking_id",
 *          type="string",
 *          description="ID de tracking",
 *          example="OR0022309461"
 *      ),
 *      @OA\Property(
 *          property="state",
 *          type="string",
 *          description="Estado de la orden",
 *          example="Registrado"
 *      ),
 *      @OA\Property(
 *          property="state_class",
 *          type="string",
 *          description="Clase CSS del state",
 *          example="primary"
 *      ),
 *      @OA\Property(
 *          property="value",
 *          type="string",
 *          format="float",
 *          description="Precio de la orden",
 *          example="88055.00"
 *      )
 * )
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'order_id',
        'shipment_id',
        'tracking_id',
        'state',
        'state_class',
        'items',
        'sender',
        'receiver',
        'value',
    ];
}
