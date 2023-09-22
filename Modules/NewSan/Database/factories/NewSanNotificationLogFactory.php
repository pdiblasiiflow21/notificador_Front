<?php

declare(strict_types=1);

namespace Modules\NewSan\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\NewSan\Entities\NewSanNotificationLog;

class NewSanNotificationLogFactory extends Factory
{
    protected $model = NewSanNotificationLog::class;

    public function definition(): array
    {
        $notified  = array_map(fn () => $this->faker->unique()->numberBetween(1, 1000), range(1, 10));
        $finalized = array_map(fn () => $this->faker->unique()->numberBetween(1, 1000), range(1, 10));

        $notifiedJson  = json_encode($notified);
        $finalizedJson = json_encode($finalized);

        return [
            'message'       => 'Se notificaron '.count($notified).' orders a la api de NewSan. Los finalizados son:'.count($finalized),
            'notified'      => $notifiedJson,
            'finalized'     => $finalizedJson,
            'response_time' => $this->faker->randomFloat(8, 0, 10000000), // Generar un float aleatorio con 8 dígitos decimales
        ];
    }
}
