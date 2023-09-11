<?php

declare(strict_types=1);

namespace App\Console;

use App\Jobs\NotifyOrdersNewSan;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->job(new NotifyOrdersNewSan())->dailyAt('06:00'); // A las 06:00 todos los días
        $schedule->job(new NotifyOrdersNewSan())->dailyAt('12:00'); // A las 12:00 todos los días
        $schedule->job(new NotifyOrdersNewSan())->dailyAt('18:00'); // A las 18:00 todos los días
        $schedule->job(new NotifyOrdersNewSan())->dailyAt('00:00'); // A las 12:30 todos los días

        $schedule->call(function () {
            DB::table('api_logs')->delete();
        })->monthlyOn(1, '00:00'); // A las 00:00 del primer dia de cada mes
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
