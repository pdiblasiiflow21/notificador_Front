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

Route::middleware(['auth:sanctum'])->prefix('v1/newsan')->group(function () {
    Route::get('notification-logs', [NewSanController::class, 'notificationLogs'])
        ->middleware(['permission:NewSan-ver_notificaciones'])
        ->name('v1.newsan.notification-logs');
    Route::get('notify-orders', [NewSanController::class, 'notifyOrders'])
        ->middleware(['permission:NewSan-correr_notificaciones'])
        ->name('v1.newsan.notifyOrders');
    Route::get('notification-logs/export/{newSanNotificationLog}', [NewSanController::class, 'exportNotificationLog'])
        ->middleware(['permission:NewSan-descargar_notificaciones'])
        ->name('v1.newsan.notification-logs-export');
});
