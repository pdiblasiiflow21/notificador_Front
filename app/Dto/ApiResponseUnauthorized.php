<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * @OA\Schema(
 *     @OA\Property(property="code", description="status code", type="integer", default="401"),
 *     @OA\Property(property="message", description="mensaje del servidor", type="string", default="Token vencido. Unauthorized"),
 * )
 */
class ApiResponseUnauthorized extends ApiResponseDto
{
}
