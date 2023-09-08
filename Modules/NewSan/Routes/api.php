<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\NewSan\Http\Controllers\V1\NewSanController;

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

Route::prefix('v1')->group(function () {
    Route::post('/iflow/token', [NewSanController::class, 'getToken'])->name('v1.iflow.getToken');
    Route::get('/iflow/get-status/{trackId}', [NewSanController::class, 'getStatusOrder'])->name('v1.iflow.getStatusOrder');
    Route::get('/iflow/get-seller-orders', [NewSanController::class, 'getSellerOrders'])->name('v1.iflow.getSellerOrders');
});
