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
use Illuminate\Support\Facades\Log;
use Modules\NewSan\Services\V1\NewSanService;

class NotifyOrdersNewSan implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
    }

    public function handle(
        IflowApiService $iflowApiService,
        NewSanService $newSanService
    ): void {
        $request = new Request();
        $request->merge([
            'username' => config('app.iflow.username'),
            'password' => config('app.iflow.password'),
        ]);

        // Obtener el token usando el servicio
        $token = $iflowApiService->getToken($request);
        Log::channel('api_newsan')->info('Respuesta de la API iflow en el schedule token: '.$token);

        if ($token) {
            try {
                request()->headers->set('Authorization', 'Bearer '.$token);

                // Usa el método del servicio NewSanService para notificar los pedidos
                $response = $newSanService->notifyOrders(request());

                Log::channel('api_newsan')->info('Se notificaron '.$response['notifications'].' orders a la api de NewSan. Los finalizados son: '.$response['finalized']);
            } catch (\Exception $e) {
                // Manejar el error
                Log::channel('api_newsan')->error('Error en la API:', [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                ]);
            }
        }
    }
}
