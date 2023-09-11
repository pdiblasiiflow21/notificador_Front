<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * @OA\Schema(
 *      schema="NotifyOrdersApiResponse",
 *      @OA\Property(property="code", description="status code", type="integer", example="200"),
 *      @OA\Property(property="shortDescription", description="mensaje del servidor", type="string", example="OK"),
 *      @OA\Property(property="longDescription", description="mensaje del servidor", type="string", example="SUCCESS"),
 * )
 */
class NotifyOrdersApiResponse extends ApiResponseDto
{
}
