<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1;

use App\Service\V1\NewSanApiService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NewSanApiController extends ApiController
{
    private $newSanApiService;

    public function __construct(NewSanApiService $newSanApiService)
    {
        $this->newSanApiService = $newSanApiService;
    }

    /**
     * @OA\Get(
     *      path="/api/v1/newsan/notifications",
     *      summary="Notifica una orden a la api de NewSan",
     *      operationId="NewSannotifications",
     *      tags={"NewSanApi"},
     *
     *      @OA\Parameter(name="IdOrder", in="query", description="order_id", required=true, @OA\Schema(type="string", default="97200000290123")),
     *      @OA\Parameter(name="IdShip", in="query", description="shipment_id", required=true, @OA\Schema(type="string", default="RRZ0000002104188")),
     *      @OA\Parameter(name="IdTrack", in="query", description="tracking_id", required=true, @OA\Schema(type="string", default="OR0022304793")),
     *      @OA\Parameter(name="Last_state", in="query", description="state", required=true, @OA\Schema(type="string", default="No Entregado")),
     *      @OA\Parameter(name="Details", in="query", description="details", required=true, @OA\Schema(type="string", default="Visitamos tu domicilio el día de hoy pero no te encontramos")),
     *      @OA\Parameter(name="State_date", in="query", description="fecha y hora (dd/mm/YYYY HH:mm)", required=true, @OA\Schema(type="string", default="09/09/2023 18:40")),
     *
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object", ref="#/components/schemas/NotifyOrdersApiResponse")),
     *      @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))),
     * )
     */
    public function notifyOrders(Request $request)
    {
        $orderData = [
            'IdOrder'    => $request->input('IdOrder'),
            'IdShip'     => $request->input('IdShip'),
            'IdTrack'    => $request->input('IdTrack'),
            'Last_state' => $request->input('Last_state'),
            'Details'    => $request->input('Details'),
            'State_date' => $request->input('State_date'),
        ];

        return $this->newSanApiService->postStatus($orderData);
    }
}
