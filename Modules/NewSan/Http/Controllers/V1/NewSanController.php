<?php

declare(strict_types=1);

namespace Modules\NewSan\Http\Controllers\V1;

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
     * @OA\Post(
     *      path="/api/v1/iflow/token",
     *      summary="get token iflow api",
     *      description="Obtener token de api iflow",
     *      tags={"IFlowApi"},
     *      @OA\RequestBody(
     *          description="User credentials",
     *          required=true,
     *          @OA\JsonContent(
     *              required={"username", "password"},
     *              @OA\Property(
     *                  property="username",
     *                  type="string",
     *                  description="The username",
     *                  example="newsan"
     *              ),
     *              @OA\Property(
     *                  property="password",
     *                  type="string",
     *                  description="The password",
     *                  example="iflow2468"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="token",
     *                  type="string",
     *                  description="The JWT token"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      )
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getToken(Request $request)
    {
        try {
            $token = $this->newSanService->getToken($request);

            return response()->json(['token' => $token]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *      security={{"sanctum": {}}},
     *      path="/api/v1/iflow/get-status/{trackId}",
     *      summary="Obtiene el estado del pedido desde la API de Iflow",
     *      description="Este método se utiliza para obtener el estado actual de un pedido en la API de Iflow.",
     *      tags={"IFlowApi"},
     *      @OA\Parameter(
     *          name="trackId",
     *          in="path",
     *          description="El identificador del pedido en el sistema de Iflow.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="Authorization",
     *          in="header",
     *          description="El token de autenticación para acceder a la API de Iflow.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Operación exitosa",
     *          @OA\JsonContent(ref="#/components/schemas/OrderStatus")
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Error interno del servidor",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="error",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     * @param mixed $trackId
     */
    public function getStatusOrder(Request $request, $trackId)
    {
        try {
            $status = $this->newSanService->getStatusOrder($trackId);

            return response()->json($status);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *      security={{"sanctum": {}}},
     *      path="/api/v1/iflow/get-seller-orders",
     *      summary="Obtiene todas las órdenes del vendedor desde la API de Iflow",
     *      operationId="getSellerOrders",
     *      tags={"IFlowApi"},
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="Nro de página para hacer la consulta a la api",
     *          required=false,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="limit",
     *          in="query",
     *          description="Cantidad de items pedidos a la api",
     *          required=false,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Operación exitosa",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Order")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Error interno del servidor"
     *      )
     * )
     */
    public function getSellerOrders(Request $request)
    {
        try {
            $orders = $this->newSanService->getSellerOrders($request);

            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json([
                'code'    => Response::HTTP_UNAUTHORIZED,
                'message' => $e->getMessage(),
        ], Response::HTTP_UNAUTHORIZED);
        }
    }
}
