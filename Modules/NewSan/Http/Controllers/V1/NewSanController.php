<?php

declare(strict_types=1);

namespace Modules\NewSan\Http\Controllers\V1;

use App\Exceptions\Api\TokenVencidoException;
use App\Http\Controllers\V1\ApiController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\NewSan\Entities\NewSanNotificationLog;
use Modules\NewSan\Services\V1\NewSanService;

class NewSanController extends ApiController
{
    private $newSanService;

    public function __construct(NewSanService $newSanService)
    {
        $this->newSanService = $newSanService;
    }

    public function notificationLogs(Request $request)
    {
        try {
            $response = $this->newSanService->notificationLogs($request);

            return response()->json([
                'code'   => Response::HTTP_OK,
                'result' => $response,
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $message    = $th->getMessage();
        }

        return response()->json(['code' => $statusCode, 'message' => $message], $statusCode);
    }

    /**
     * @OA\Get(
     *      security={{"sanctum": {}}},
     *      path="/api/v1/newsan/notify-orders",
     *      summary="Notifica las órdenes de NewSan obtenidas de la API de Iflow a la api de NewSan",
     *      operationId="notifyOrders",
     *      tags={"NewSan"},
     *
     *      @OA\Parameter(name="pages", in="query", description="Cantidad de páginas para hacer la consulta a la api de iflow para el cliente NewSan", required=false, @OA\Schema(type="integer", format="int64", default=1)),
     *      @OA\Parameter(name="limit", in="query", description="Cantidad de ordenes por página pedidos a la api de iflow para el cliente NewSan", required=false, @OA\Schema(type="integer", format="int64", default=100)),
     *
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object", ref="#/components/schemas/NotifyOrdersResponse")),
     *      @OA\Response(response=401, description="Error: Unauthorized", @OA\JsonContent(type="object", ref="#/components/schemas/ApiResponseUnauthorized")),
     *      @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))),
     * )
     */
    public function notifyOrders(Request $request)
    {
        try {
            $response = $this->newSanService->notifyOrders($request);

            return response()->json([
                'code'    => Response::HTTP_OK,
                'message' => 'Se notificaron '.$response['notifications'].' orders a la api de NewSan. Los finalizados son: '.$response['finalized'],
            ], Response::HTTP_OK);
        } catch (TokenVencidoException $e) {
            $statusCode = Response::HTTP_UNAUTHORIZED;
            $message    = $e->getMessage();
        } catch (\Throwable $th) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $message    = $th->getMessage();
        }

        return response()->json(['code' => $statusCode, 'message' => $message], $statusCode);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/newsan/notification-logs/export/{newSanNotificationLog}",
     *      summary="Descarga los registros de NewSan_orders_informed en formato CSV",
     *      operationId="exportNotifyOrders",
     *      tags={"NewSan"},
     *
     *      @OA\Parameter(name="newSanNotificationLog", in="path", description="Id de newSanNotificationLog que se quiere descargar", required=true, explode=true, @OA\Schema(type="integer", format="int64")),
     *
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object", ref="#/components/schemas/NotifyOrdersResponse")),
     *      @OA\Response(response=401, description="Error: Unauthorized", @OA\JsonContent(type="object", ref="#/components/schemas/ApiResponseUnauthorized")),
     *      @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))),
     * )
     */
    public function exportNotificationLog(Request $request, NewSanNotificationLog $newSanNotificationLog)
    {
        try {
            $columns = [
                'API ID'      => 'api_id',
                'ORDER ID'    => 'order_id',
                'SHIPMENT ID' => 'shipment_id',
                'TRACKING ID' => 'tracking_id',
                'STATE ID'    => 'state_id',
                'MESSAGE'     => 'message',
                'STATE DATE'  => 'state_date',
                'FINALIZED'   => 'finalized',
                'UPDATED AT'  => 'updated_at',
            ];

            return $this->newSanService->exportNotificationLog($newSanNotificationLog->id, $columns);
        } catch (\Throwable $th) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $message    = $th->getMessage();
        }

        return response()->json(['code' => $statusCode, 'message' => $message], $statusCode);
    }
}
