<?php

declare(strict_types=1);

namespace Modules\NewSan\Entities;

use Carbon\Carbon;
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

    public $incrementing = false;

    protected static function newFactory()
    {
        return NewSanOrderInformedFactory::new();
    }

    public const HASHMAP_STATE_NAME_TO_STATE_NAME_UPPER = [
        'Registrado'                => 'REGISTRADO',
        'Retirado'                  => 'RETIRADO',
        'Descargado'                => 'DESCARGADO',
        'Entregado'                 => 'ENTREGADO',
        'No Entregado'              => 'NO_ENTREGADO',
        'Despachado a Nodo Interno' => 'DESPACHADO_A_NODO_INTERNO',
        'Arribo a Nodo'             => 'ARRIBO_A_NODO',
        'Devolución'                => 'DEVOLUCION',
        'Pedido en Distribución'    => 'PEDIDO_EN_DISTRIBUCION',
        'En proceso de devolucion'  => 'EN_PROCESO_DEVOLUCION',
        'Devolucion a Central'      => 'DEVOLUCION_A_CENTRAL',
        'Pactado'                   => 'PACTADO',
        'Cancelado'                 => 'CANCELADO',
        'Devolución No Entregada'   => 'DEVOLUCION_NO_ENTREGADA',
        'Contingencia'              => 'CONTINGENCIA',
    ];

    protected $fillable = [
        'api_id',
        'order_id',
        'shipment_id',
        'tracking_id',
        'state_id',
        'state_name',
        'message',
        'state_date',
        'last_notified_state',
        'finalized',
    ];

    protected $casts = [
        'finalized' => 'boolean',
    ];

    public function markAsFinalized()
    {
        $this->finalized = true;
        $this->save();
    }

    public static function transformDateTime($fecha)
    {
        return Carbon::createFromFormat('d/m/Y H:i', $fecha)->format('Y-m-d\TH:i');
    }
}
