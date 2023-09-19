<?php

declare(strict_types=1);

namespace Modules\NewSan\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\NewSan\Entities\NewSanOrder;

class NewSanOrderFactory extends Factory
{
    protected $model = NewSanOrder::class;

    public function definition(): array
    {
        return [
            'api_id'      => $this->faker->unique()->randomNumber(8),
            'order_id'    => $this->faker->unique()->bothify('###########'),
            'shipment_id' => $this->faker->unique()->bothify('RRZ#######'),
            'tracking_id' => $this->faker->unique()->bothify('OR#######'),
            'state'       => $this->faker->randomElement(['No Entregado', 'Entregado', 'En Tránsito']),
            'date'        => $this->faker->date('d/m/Y'),
            'finalized'   => $this->faker->randomElement([NewSanOrder::NO_FINALIZADO, NewSanOrder::FINALIZADO]),
        ];
    }
}
