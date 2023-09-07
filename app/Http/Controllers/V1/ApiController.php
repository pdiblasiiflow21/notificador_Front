<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="API v1",
 *      description="Details API",
 *      @OA\Server(
 *          url=L5_SWAGGER_CONST_HOST,
 *          description="API"
 *      )
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer"
 * )
 */
class ApiController extends Controller
{
}
