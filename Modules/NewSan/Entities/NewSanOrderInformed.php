<?php

declare(strict_types=1);

namespace Modules\NewSan\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\NewSan\Database\factories\NewSanOrderInformedFactory;

/**
 * @OA\Schema(
 *      schema="NewSanOrderInformed",
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
class NewSanOrderInformed extends Model
{
    use HasFactory;

    protected $table = 'NewSan_orders_informed';

    protected $primaryKey = 'api_id';

    protected static function newFactory()
    {
        return NewSanOrderInformedFactory::new();
    }

    public const REGISTRADO = 'Registrado';

    public const DESCARGADO = 'Descargado';

    public const DESPACHADO = 'Despachado a Nodo Interno';

    public const ARRIBADO = 'Arribo a Nodo';

    public const PACTADO = 'Pactado';

    public const PEDIDO = 'Pedido en Distribución';

    public const ENTREGADO = 'Entregado';

    public const PEDIDO_EN_DEVOLUCION = 'En proceso de devolucion';

    public const NO_ENTREGADO = 'No Entregado';

    protected $fillable = [
        'api_id',
        'order_id',
        'shipment_id',
        'tracking_id',
        'state_id',
        'state_name',
        'message',
        'state_date',
        'finalized',
    ];

    protected $casts = [
        'finalized' => 'boolean',
    ];

    public static function getUnfinalizedOrders()
    {
        return self::where('finalized', false)->get();
    }

    public function markAsFinalized()
    {
        $this->finalized = true;
        $this->save();
    }
}
