<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1;

use App\Http\Requests\Permissions\CreatePermissionRequest;
use Spatie\Permission\Models\Permission;

class PermissionsController extends ApiController
{
    public function index()
    {
        $permissions = Permission::select('id', 'name', 'created_at', 'updated_at')->get();

        return response()->json($permissions);
    }

    public function store(CreatePermissionRequest $request)
    {
        $validatedData = $request->validated();

        // Crear el nuevo permiso
        $permission = Permission::create(['name' => $validatedData['name'], 'guard_name' => 'web']);

        return response()->json(['message' => 'Permiso creado con éxito.', 'permission' => $permission]);
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->json(['message' => 'Permission deleted successfully.'], 200);
    }
}
