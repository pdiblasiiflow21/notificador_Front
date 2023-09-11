<?php

declare(strict_types=1);

namespace App\Console;

use App\Jobs\NotifyOrdersNewSan;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->job(new NotifyOrdersNewSan())
            ->dailyAt('12:15'); // A las 12:15 todos los días

        $schedule->job(new NotifyOrdersNewSan())
            ->dailyAt('12:30'); // A las 12:30 todos los días
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
