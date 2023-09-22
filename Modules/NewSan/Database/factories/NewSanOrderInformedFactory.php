<?php

declare(strict_types=1);

namespace Modules\NewSan\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\NewSan\Entities\NewSanOrderInformed;

class NewSanOrderInformedFactory extends Factory
{
    protected $model = NewSanOrderInformed::class;

    public function definition(): array
    {
        return [
            'api_id'      => $this->faker->unique()->randomNumber(),
            'order_id'    => $this->faker->uuid,
            'shipment_id' => $this->faker->uuid,
            'tracking_id' => $this->faker->uuid,
            'state_id'    => $this->faker->word,
            'state_name'  => $this->faker->word,
            'message'     => $this->faker->sentence,
            'state_date'  => $this->faker->date('Y-m-d H:i:s'),
            'finalized'   => $this->faker->boolean,
        ];
    }
}
