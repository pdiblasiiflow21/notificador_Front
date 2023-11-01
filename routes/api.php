<?php

declare(strict_types=1);

use App\Http\Controllers\V1\IflowApiController;
use App\Http\Controllers\V1\NewSanApiController;
use App\Http\Controllers\V1\PermissionsController;
use App\Http\Controllers\V1\RolesController;
use App\Http\Controllers\V1\UsersController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

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
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        $user = $request->user();

        return response()->json([
            'user'        => $user,
            'permissions' => $user->getPermissionsViaRoles()->pluck('name'),
        ]);
    });
    Route::get('/users-with-roles', function (Request $request) {
        $users = User::with('roles')->get();

        return response()->json($users);
    });
    Route::get('/permissions-by-role', function (Request $request) {
        $roles = Role::with('permissions')->get();

        return response()->json($roles);
    });
});

Route::prefix('users')->middleware(['auth:sanctum', 'role:administrador'])->group(function () {
    Route::get('/', [UsersController::class, 'index'])->name('users.index');
    Route::post('/', [UsersController::class, 'store'])->name('users.store');
    Route::put('/{user}', [UsersController::class, 'update'])->name('users.update');
    Route::post('/{id}/toggle-status', [UsersController::class, 'toggleStatus'])->name('users.toggle-status');
});

Route::prefix('roles')->middleware(['auth:sanctum', 'role:administrador'])->group(function () {
    Route::get('/', [RolesController::class, 'index'])->name('roles.index');
    Route::post('/', [RolesController::class, 'store'])->name('roles.create');
    Route::get('/{role}', [RolesController::class, 'show'])->name('roles.show');
    Route::put('/{role}/permissions', [RolesController::class, 'updateRolePermissions'])->name('roles.updateRolePermissions');
    Route::delete('{role}', [RolesController::class, 'destroy'])->name('roles.destroy');
});

Route::prefix('permissions')->middleware(['auth:sanctum', 'role:administrador'])->group(function () {
    Route::get('/', [PermissionsController::class, 'index'])->name('permissions.index');
    Route::post('/', [PermissionsController::class, 'store'])->name('permissions.store');
    Route::delete('/{permission}', [PermissionsController::class, 'destroy'])->name('permissions.destroy');
});

Route::prefix('v1/iflow')->group(function () {
    Route::post('token', [IflowApiController::class, 'getToken'])->name('v1.iflow.getToken');
    Route::get('get-status-order/{trackId}', [IflowApiController::class, 'getStatusOrder'])->name('v1.iflow.getStatusOrder');
    Route::get('get-seller-orders', [IflowApiController::class, 'getSellerOrders'])->name('v1.iflow.getSellerOrders');
});

Route::prefix('v1/newsan')->group(function () {
    Route::get('notifications', [NewSanApiController::class, 'notifyOrders'])->name('v1.iflow.getToken');
});
