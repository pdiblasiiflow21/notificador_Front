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

Route::prefix('v1/newsan')->group(function () {
    Route::get('notification-logs', [NewSanController::class, 'notificationLogs'])->name('v1.newsan.notification-logs');
    Route::get('notify-orders', [NewSanController::class, 'notifyOrders'])->name('v1.newsan.notifyOrders');
    Route::get('notification-logs/export/{newSanNotificationLog}', [NewSanController::class, 'exportNotificationLog'])->name('v1.newsan.notification-logs-export');
});
