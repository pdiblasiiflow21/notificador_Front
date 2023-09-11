<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1;

use App\Exceptions\Api\CredencialesInvalidasException;
use App\Exceptions\Api\TokenVencidoException;
use App\Service\V1\IflowApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IflowApiController extends ApiController
{
    private $iflowApiService;

    public function __construct(IflowApiService $iflowApiService)
    {
        $this->iflowApiService = $iflowApiService;
    }

    /**
     * @OA\Post(
     *      path="/api/v1/iflow/token",
     *      summary="get token iflow api",
     *      description="Obtener token de api iflow",
     *      tags={"IFlowApi"},
     *
     *      @OA\RequestBody(
     *          description="User credentials",
     *          required=true,
     *          @OA\JsonContent(
     *              required={"username", "password"},
     *              @OA\Property(property="username", type="string", description="The username", example="newsanprod"),
     *              @OA\Property(property="password", type="string", description="The password", example="New5an.2021&")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="code", type="integer", description="Código HTTP", example="200"),
     *              @OA\Property(property="result", type="string", description="Token", example="eyJhbGciOiJSUzI1NiJ9.eyJyb2xlcyI6WyJST0xFX0NVU1RPTUVSIiwiUk9MRV9BUElfQ0xJRU5UIiwiUk9MRV9BUElfQ0xJRU5UX0NBTkNFTF9PUkRFUiIsIlJPTEVfQVBJX0NMSUVOVF9DQU5DRUxfU0hJUFBJTkciLCJST0xFX0FQSV9DTElFTlRfTUVSQ0hBTlRfT1JERVJfQUREIiwiUk9MRV9BUElfQ0xJRU5UX1NISVBQSU5HX1JFVFVSTiIsIlJPTEVfQVBJX0NMSUVOVF9TSElQUElOR19CQVRDSF9QUklOVElORyIsIlJPTEVfQVBJIl0sInVzZXJuYW1lIjoibmV3c2FucHJvZCIsInRlcm1zX2FjY2VwdGVkIjp0cnVlLCJwcmVwYWdvIjpmYWxzZSwibG9naXN0aWNhX2ludmVyc2EiOmZhbHNlLCJkZWZhdWx0X2Zvcm1hdCI6InBkZiIsInVzZXJfdHlwZSI6IkNsaWVudCIsImlhdCI6MTY5NDI3NTgxOCwiZXhwIjoxNjk0Mjc5NDE4fQ.sxKx4Z6fgy2lHDQ_Hb_TDc7O_5qT94Ma80Wg3WvGL4aHetg7S1RJDhabPvrfGaEIp9_JtG-iHq88AkW_mCQkvOSsoDxAv3-dtOJzMG3fj78tBdv6rDofYQWxP5l__Yeh44fBQgchtpTOnZS9E4X-QJjWf7yNJYKhpacZ9sQVdGQk4FB04ii8DY49jzsZ4qGKl7S1WwyGPQlfEjVlzFXlmVZiER9orf6D5k6wUvCuThwK2y2jVqPoYu1Obb7sycaV0ZQytR8-g5ZhuGnYN0q8Q5T7NxCZP2Oo1XXRFeYc6nTbFSi5PskUe42BzYOi4EhpBqvGbB4FJINQZNow2Jg7azPYLy2EfTbjji9dSmXO31bmMhWXpQ6RHFjVd4i2cOvvdf1DEoUKUHpJcn2LRLOhsJW9Nj3IsgV4RmEEf-c5TbcQCYV1vfK-3BhtiSG4_8W2QbZHD_84sDURrPp05g83kV8zlCgLSFAegjPoMiFklk0ST9TzFSoz5bXfY2M9Wa81MpxR0GcpYRTSBdyqFH3QAC_t0fc6okxqtxuFMQFp6dlQ5wc0-aZepvFjaJE6poOx-oOxnWhWkF_VfA4f96h4v7f9IIdiP794Rpm8Q0KJs965kkRDv5uHSC3KMXIcYGFdx9dqC-EO6OqF1sRa3dU19PUQjVPOF2xF75VW9eASJso"))
     *      ),
     *      @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))),
     * )
     *
     * @param Request $request
     */
    public function getToken(Request $request): JsonResponse
    {
        try {
            $token = $this->iflowApiService->getToken($request);

            return response()->json([
                'code'   => Response::HTTP_OK,
                'result' => $token,
            ], Response::HTTP_OK);
        } catch (CredencialesInvalidasException | TokenVencidoException $e) {
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
     *      security={{"sanctum": {}}},
     *      path="/api/v1/iflow/get-status-order/{trackId}",
     *      summary="Obtiene el estado de un pedido desde la API de Iflow",
     *      description="Este método se utiliza para obtener el estado actual de un pedido en la API de Iflow.",
     *      tags={"IFlowApi"},
     *
     *      @OA\Parameter(name="trackId", in="path", description="El identificador del pedido en el sistema de Iflow. (Tracking ID)", required=true, @OA\Schema(type="string", example="OR0022306264")),
     *
     *      @OA\Response(response=200, description="Operación exitosa", @OA\JsonContent(ref="#/components/schemas/OrderStatusResponse")),
     *      @OA\Response(response=401, description="Error: Unauthorized", @OA\JsonContent(type="object", ref="#/components/schemas/ApiResponseUnauthorized")),
     *      @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))),
     * )
     * @param mixed $trackId
     */
    public function getStatusOrder(Request $request, $trackId)
    {
        try {
            $status = $this->iflowApiService->getStatusOrder($trackId);

            return response()->json($status);
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
     *      security={{"sanctum": {}}},
     *      path="/api/v1/iflow/get-seller-orders",
     *      summary="Obtiene un conjunto de órdenes del vendedor desde la API de Iflow",
     *      operationId="getSellerOrders",
     *      tags={"IFlowApi"},
     *
     *      @OA\Parameter(name="page", in="query", description="Nro de página para hacer la consulta a la api", required=false, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="limit", in="query", description="Cantidad de items pedidos a la api", required=false, @OA\Schema(type="integer")),
     *
     *      @OA\Response(response=200, description="Operación exitosa", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/SellerOrdersResponse"))),
     *      @OA\Response(response=401, description="Error: Unauthorized", @OA\JsonContent(type="object", ref="#/components/schemas/ApiResponseUnauthorized")),
     *      @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))),
     * )
     */
    public function getSellerOrders(Request $request)
    {
        try {
            $orders = $this->iflowApiService->getSellerOrders($request);

            return response()->json($orders);
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
