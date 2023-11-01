<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1;

use App\Models\User;
use Illuminate\Http\Request;

class RolesAndPermissionsController extends ApiController
{
    public function syncPermissions(Request $request, User $user)
    {
        // Validar el request para asegurarte de que se envíen permisos válidos
        $validatedData = $request->validate([
            'permissions'   => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $user->syncPermissions($validatedData['permissions']);

        return response()->json(['message' => 'Permisos sincronizados con éxito.']);
    }
}
