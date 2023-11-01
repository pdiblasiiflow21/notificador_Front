<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1;

use App\Http\Requests\Roles\CreateRoleRequest;
use App\Service\V1\RolService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolesController extends ApiController
{
    private $rolService;

    public function __construct(RolService $rolService)
    {
        $this->rolService = $rolService;
    }

    public function index(Request $request)
    {
        $response = $this->rolService->index($request);

        return response()->json($response, $response['code']);
    }

    public function show(Role $role)
    {
        $role = $this->rolService->getRoleWithPermissions($role);

        return response()->json($role);
    }

    public function updateRolePermissions(Request $request, Role $role)
    {
        $response = $this->rolService->updateRolePermissions($request, $role);

        if (isset($response['message'])) {
            return response()->json(['message' => $response['message']], 200);
        }

        return response()->json(['error' => $response['error']], $response['code']);
    }

    public function store(CreateRoleRequest $request)
    {
        $data = $request->validated();

        $response = $this->rolService->storeRole($data);

        if (isset($response['message'])) {
            return response()->json([
                'message' => $response['message'],
                'role'    => $response['role'],
            ], $response['code']);
        }

        return response()->json(['error' => $response['error']], $response['code']);
    }

    public function destroy(Role $role)
    {
        $response = $this->rolService->destroyRole($role);

        return response()->json(['message' => $response['message']], $response['code']);
    }
}
