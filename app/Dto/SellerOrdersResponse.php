<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * @OA\Schema(
 *      schema="SellerOrdersResponse",
 *      @OA\Property(property="code", description="status code", type="integer", example="200"),
 *      @OA\Property(property="message", description="mensaje del servidor", type="string", example="OK"),
 *      @OA\Property(property="count", description="Cantidad de Ordenes", type="integer", example="1"),
 *      @OA\Property(
 *          property="results",
 *          type="array",
 *          @OA\Items(
 *              type="object",
 *              @OA\Property(property="id", type="integer", example="22537629"),
 *              @OA\Property(property="order_id", type="string", example="97200000292615"),
 *              @OA\Property(property="shipment_id", type="string", example="RRZ0000002105362"),
 *              @OA\Property(property="tracking_id", type="string", example="OR0022306344"),
 *              @OA\Property(property="state", type="string", example="Entregado"),
 *              @OA\Property(property="state_class", type="string", example="success"),
 *              @OA\Property(
 *                  property="items",
 *                  type="array",
 *                  @OA\Items(
 *                      type="object",
 *                      @OA\Property(property="id", type="integer", example="14672570"),
 *                      @OA\Property(property="item", type="string", example="TOAT39DN"),
 *                      @OA\Property(property="sku", type="string", example="94TOAT39DN"),
 *                      @OA\Property(property="quantity", type="integer", example="1"),
 *                      @OA\Property(property="weight", nullable=true, example="null"),
 *                      @OA\Property(property="length", nullable=true, example="null"),
 *                      @OA\Property(property="height", nullable=true, example="null"),
 *                      @OA\Property(property="width", nullable=true, example="null"),
 *                      @OA\Property(property="value", nullable=true, example="null"),
 *                  )
 *              ),
 *              @OA\Property(
 *                  property="sender",
 *                  type="object",
 *                  @OA\Property(property="id", type="integer", example="19075"),
 *                  @OA\Property(property="user_id", type="string", example="T61a656e92378a"),
 *                  @OA\Property(property="nickname", type="string", example="newsanprod"),
 *                  @OA\Property(property="first_name", type="string", example="Newsan Producción"),
 *                  @OA\Property(property="last_name", type="string", example="Newsan Producción"),
 *                  @OA\Property(property="email", type="string", example="trafico_deposito@newsan.com.ar"),
 *                  @OA\Property(property="corporate_name", nullable=true, example="null"),
 *              ),
 *              @OA\Property(
 *                  property="receiver",
 *                  type="object",
 *                  @OA\Property(property="id", type="integer", example="22195358"),
 *                  @OA\Property(property="user_id", nullable=true, example="null"),
 *                  @OA\Property(property="nickname", nullable=true, example="null"),
 *                  @OA\Property(property="first_name", type="string", example=""),
 *                  @OA\Property(property="last_name", type="string", example="Pablo Javier  Ramirez"),
 *                  @OA\Property(property="receiver_name", type="string", example="Pablo Javier  Ramirez"),
 *                  @OA\Property(property="receiver_phone", type="string", example="+541134765851"),
 *                  @OA\Property(property="email", type="string", example="interceptor+hxgdfsfwjpopuwfyav@producteca.com"),
 *                  @OA\Property(property="company", nullable=true, example="null"),
 *                  @OA\Property(property="agency", nullable=true, example="null"),
 *                  @OA\Property(property="address_street_name", type="string", example="Juan Maria gutierrez 2978 Casa chalet Pte. Peron y cocha"),
 *                  @OA\Property(property="address_street_number", type="string", example="-"),
 *                  @OA\Property(property="address_other_info", nullable=true, example="null"),
 *                  @OA\Property(property="address_neighborhood_name", type="string", example=" "),
 *                  @OA\Property(property="address_zip_code", type="string", example="1614"),
 *                  @OA\Property(property="address_city", type="string", example="VILLA DE MAYO"),
 *                  @OA\Property(property="address_state", type="string", example="BUENOS AIRES"),
 *                  @OA\Property(property="address_country", type="string", example="Argentina"),
 *                  @OA\Property(property="address_floor", nullable=true, example="null"),
 *                  @OA\Property(property="address_apartment", nullable=true, example="null"),
 *                  @OA\Property(property="address_between_1", type="string", example=" "),
 *                  @OA\Property(property="address_between_2", nullable=true, example="null"),
 *                  @OA\Property(property="address_latitude", type="string", example="-34.50881400"),
 *                  @OA\Property(property="address_longitude", type="string", example="-58.67944800"),
 *              ),
 *              @OA\Property(property="date", type="string", example="06/09/2023"),
 *              @OA\Property(property="order_date", type="string", example="06/09/2023 17:23"),
 *              @OA\Property(property="value", type="string", example="17545.00"),
 *              @OA\Property(property="delivery_shift", type="string", example="1"),
 *              @OA\Property(property="delivery_shift_description", type="string", example="#1 Todo el día (08:00 - 08:00)"),
 *              @OA\Property(property="shipping_cost", type="string", example="1222.48"),
 *              @OA\Property(property="estimated_delivery_date", type="string", example="16/09/2023"),
 *              @OA\Property(property="estimated_delivery_date_min", type="string", example="08/09/2023"),
 *              @OA\Property(property="estimated_delivery_date_max", type="string", example="11/09/2023"),
 *              @OA\Property(property="channel", type="string", example="iflow"),
 *              @OA\Property(property="mode", nullable=true, example="null"),
 *              @OA\Property(
 *                  property="modes",
 *                  type="array",
 *                  @OA\Items(
 *                      type="object",
 *                      @OA\Property(property="id", type="integer", example="1"),
 *                      @OA\Property(property="name",type="string", example="Colecta"),
 *                      @OA\Property(property="description", type="string", example="Retiros que se hacen desde la dirección de Origen del paquete, hasta una Planta."),
 *                  )
 *              ),
 *              @OA\Property(property="created_by", type="string", example="newsanprod"),
 *              @OA\Property(property="created_at", type="string", example="06/09/2023 17:23:32",),
 *              @OA\Property(property="updated_by", nullable=true, example="null"),
 *              @OA\Property(property="updated_at", type="string", example="09/09/2023 13:54:20",),
 *              @OA\Property(property="registered", type="string", example="06/09/2023 17:23:32",),
 *              @OA\Property(property="processed", type="string", example="06/09/2023 17:23:32",),
 *              @OA\Property(property="planned", type="string", example=""),
 *              @OA\Property(property="pickedup", type="string", example="07/09/2023 11:05:37",),
 *              @OA\Property(property="notified", type="string", example=""),
 *              @OA\Property(property="discharged", type="string", example="07/09/2023 15:39:13",),
 *              @OA\Property(property="ready", type="string", example=""),
 *              @OA\Property(property="dispatched", type="string", example=""),
 *              @OA\Property(property="delivered", type="string", example="09/09/2023 13:54:19",),
 *              @OA\Property(property="not_delivered", type="string", example=""),
 *              @OA\Property(property="cancelled", type="boolean", example=false),
 *              @OA\Property(property="printable", type="boolean", example=false),
 *              @OA\Property(property="printed_by_client", type="boolean", example=true),
 *              @OA\Property(property="height", type="string", example="18"),
 *              @OA\Property(property="width", type="string", example="15"),
 *              @OA\Property(property="weight", type="string", example="1350"),
 *              @OA\Property(property="length", type="string", example="28"),
 *              @OA\Property(property="volume", type="string", example="7560"),
 *              @OA\Property(
 *                  property="sender_address",
 *                  type="object",
 *                  @OA\Property(property="id", type="integer", example="148114"),
 *                  @OA\Property(
 *                      property="sender",
 *                      type="object",
 *                      @OA\Property(property="id", type="integer", example="148114"),
 *                      @OA\Property(property="user_id", type="integer", example="T61a656e92378a"),
 *                      @OA\Property(property="nickname", type="integer", example="newsanprod"),
 *                      @OA\Property(property="first_name", type="integer", example="Newsan Producción"),
 *                      @OA\Property(property="last_name", type="integer", example="Newsan Producción"),
 *                      @OA\Property(property="email", type="integer", example="trafico_deposito@newsan.com.ar"),
 *                      @OA\Property(property="corporate_name", nullable=true, example="null"),
 *                  ),
 *                  @OA\Property(property="street_name", type="string", example="ROQUE PÉREZ"),
 *                  @OA\Property(property="street_number", type="string", example="3650"),
 *                  @OA\Property(property="floor", nullable=true, example="null"),
 *                  @OA\Property(property="apartment", nullable=true, example="null"),
 *                  @OA\Property(property="other_info", nullable=true, example="null"),
 *                  @OA\Property(property="neighborhood_name", type="string", example="Saavedra"),
 *                  @OA\Property(property="zip_code", type="string", example="1430"),
 *                  @OA\Property(property="state", type="string", example="CAPITAL FEDERAL"),
 *                  @OA\Property(property="city", type="string", example="Ciudad Autónoma de Buenos Aires"),
 *                  @OA\Property(property="alias", type="string", example="newsanprod"),
 *                  @OA\Property(property="active", type="boolean", example=true),
 *              ),
 *              @OA\Property(property="delivery_mode", type="string" ,example="Puerta a Puerta"),
 *              @OA\Property(property="pickup_point_code", type="string" ,example=""),
 *              @OA\Property(property="carrier_external_name", type="string" ,example=""),
 *              @OA\Property(property="shipping_service", nullable=true, example="null"),
 *              @OA\Property(property="type", nullable=true, example="null"),
 *              @OA\Property(property="items_quantity", type="integer" ,example="1"),
 *              @OA\Property(property="print_png_url", type="string" ,example="http://api.iflow21.com/api/v1/public/shipping/print/5644f54dd9356896.png"),
 *              @OA\Property(property="print_url", type="string" ,example="http://api.iflow21.com/api/v1/client/shipping/print/22537629/RRZ0000002105362.pdf"),
 *              @OA\Property(property="can_return", type="boolean" ,example=false),
 *              @OA\Property(property="can_request_edit", type="boolean" ,example=false),
 *              @OA\Property(property="can_batch_shipping_printing", type="boolean" ,example=false),
 *              @OA\Property(property="order_multiple_shippings", type="boolean", example=true),
 *              @OA\Property(property="estimated_delivery_date_range", type="string", example=""),
 *          ),
 *      ),
 *      @OA\Property(
 *          property="pagination",
 *          type="object",
 *          @OA\Property(property="total", type="integer", example="62787"),
 *          @OA\Property(property="pages", type="integer", example="62787"),
 *          @OA\Property(property="current", type="string", example="1"),
 *          @OA\Property(property="limit", type="string", example="100"),
 *      ),
 * )
 */
class SellerOrdersResponse extends ApiResponseDto
{
}