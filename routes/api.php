<?php

declare(strict_types=1);

use App\Http\Controllers\V1\ApiController;
use App\Http\Controllers\V1\IflowApiController;
use App\Http\Controllers\V1\NewSanApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    $user = $request->user();

    return response()->json([
        'user'        => $user,
        'permissions' => $user->getPermissionsViaRoles()->pluck('name'),
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/permissions', [ApiController::class, 'getPermissions']);
});

Route::prefix('v1/iflow')->group(function () {
    Route::post('token', [IflowApiController::class, 'getToken'])->name('v1.iflow.getToken');
    Route::get('get-status-order/{trackId}', [IflowApiController::class, 'getStatusOrder'])->name('v1.iflow.getStatusOrder');
    Route::get('get-seller-orders', [IflowApiController::class, 'getSellerOrders'])->name('v1.iflow.getSellerOrders');
});

Route::prefix('v1/newsan')->group(function () {
    Route::get('notifications', [NewSanApiController::class, 'notifyOrders'])->name('v1.iflow.getToken');
});
