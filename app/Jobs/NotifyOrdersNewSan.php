<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Service\V1\IflowApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class NotifyOrdersNewSan implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
    }

    public function handle(IflowApiService $iflowApiService): void
    {
        $request = new Request();
        $request->merge([
            'username' => config('app.iflow.username'),
            'password' => config('app.iflow.password'),
        ]);

        // Obtener el token usando el servicio
        $token = $iflowApiService->getToken($request);

        if ($token) {
            // Llamar al endpoint dentro de tu sistema
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
            ])->get(route('v1.newsan.notifyOrders'));

            if ($response->successful()) {
                // Manejar la respuesta
            }
            // Manejar el error
        }
    }
}
