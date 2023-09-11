<?php

declare(strict_types=1);

namespace Modules\NewSan\Http\Controllers\V1;

use App\Exceptions\Api\TokenVencidoException;
use App\Http\Controllers\V1\ApiController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\NewSan\Services\V1\NewSanService;

class NewSanController extends ApiController
{
    private $newSanService;

    public function __construct(NewSanService $newSanService)
    {
        $this->newSanService = $newSanService;
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
            $numberSuccessfulNotifications = $this->newSanService->notifyOrders($request);

            return response()->json([
                'code'    => Response::HTTP_OK,
                'message' => "Se notificaron $numberSuccessfulNotifications orders a la api de NewSan.",
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
}
