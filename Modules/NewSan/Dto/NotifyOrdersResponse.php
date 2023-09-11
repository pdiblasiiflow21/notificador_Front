<?php

declare(strict_types=1);

namespace Modules\NewSan\Dto;

use App\Dto\ApiResponseDto;

/**
 * @OA\Schema(
 *      schema="NotifyOrdersResponse",
 *      @OA\Property(property="code", description="status code", type="integer", example="200"),
 *      @OA\Property(property="message", description="mensaje del servidor", type="string", example="Se notificaron 1 orders a la api de NewSan."),
 * )
 */
class NotifyOrdersResponse extends ApiResponseDto
{
}
