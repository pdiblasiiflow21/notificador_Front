<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * @OA\Schema(
 *      schema="OrderStatusResponse",
 *      @OA\Property(property="code", description="status code", type="integer", default="200"),
 *      @OA\Property(property="message", description="mensaje del servidor", type="string", default="OK"),
 *      @OA\Property(property="count", description="Cantidad de Ordenes", type="integer", default="1"),
 *      @OA\Property(
 *          property="results",
 *          type="object",
 *          @OA\Property(property="tracking_id", type="string", example="OR0022306264"),
 *          @OA\Property(
 *              property="shippings",
 *              type="array",
 *              @OA\Items(
 *                  type="object",
 *                  @OA\Property(property="shipment_id", type="string", example="RRZ0000002105282"),
 *                  @OA\Property(
 *                      property="state",
 *                      type="object",
 *                      @OA\Property(property="state_name", type="string", example="Entregado"),
 *                      @OA\Property(property="state_id", type="integer", example="25"),
 *                      @OA\Property(property="details", type="string", example="!Tu pedido fue entregado! !Que lo disfrutes!"),
 *                      @OA\Property(property="state_date", type="string", example="08/09/2023 16:18"),
 *                      @OA\Property(
 *                          property="reason",
 *                          type="array",
 *                          @OA\Items(type="string", example="Ni idea")
 *                      )
 *                  ),
 *                  @OA\Property(
 *                      property="states",
 *                      type="array",
 *                      @OA\Items(
 *                          type="object",
 *                          @OA\Property(property="state_name", type="string", example="Registrado"),
 *                          @OA\Property(property="state_id", type="integer", example="1"),
 *                          @OA\Property(property="details", type="string", example="Tu pedido está siendo preparado por el vendedor"),
 *                          @OA\Property(property="state_date", type="string", example="06/09/2023 17:22"),
 *                          @OA\Property(
 *                              property="reason",
 *                              type="array",
 *                              @OA\Items(type="string", example="Ni idea")
 *                          )
 *                      )
 *                  )
 *              )
 *          )
 *      )
 * )
 */
class OrderStatusResponse extends ApiResponseDto
{
}
