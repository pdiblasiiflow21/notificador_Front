<?php

declare(strict_types=1);

namespace Modules\NewSan\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\NewSan\Database\factories\NewSanOrderFactory;

class NewSanOrder extends Model
{
    use HasFactory;

    protected $table = 'NewSan_orders';

    protected $primaryKey = 'api_id';

    public $incrementing = false;

    public const NO_FINALIZADO = 0;

    public const FINALIZADO = 1;

    protected static function newFactory()
    {
        return NewSanOrderFactory::new();
    }

    public const REGISTRADO = 'Registrado';

    public const RETIRADO = 'Retirado';

    public const DESCARGADO = 'Descargado';

    public const DESPACHADO = 'Despachado a Nodo Interno';

    public const ARRIBADO = 'Arribo a Nodo';

    public const PEDIDO = 'Pedido en Distribución';

    public const PACTADO = 'Pactado';

    public const ENTREGADO = 'Entregado';

    public const NO_ENTREGADO = 'No Entregado';

    public const PEDIDO_EN_DEVOLUCION = 'En proceso de devolucion';

    public const DEVOLUCION_A_CENTRAL = 'Devolucion a Central';

    public const STATES_WITH_FINALIZED_TRUE = [
        self::ENTREGADO,
        self::PEDIDO_EN_DEVOLUCION,
    ];

    protected $fillable = [
        'api_id',
        'order_id',
        'shipment_id',
        'tracking_id',
        'state',
        'date',
        'finalized',
    ];

    protected $casts = [
        'finalized' => 'boolean',
    ];
}
