<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * @OA\Schema(
 *     @OA\Property(property="code", description="status code", type="integer", default="200"),
 * )
 */
class ApiResponseOk extends ApiResponseDto
{
}
