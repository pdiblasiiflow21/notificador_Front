<?php

declare(strict_types=1);

namespace App\Service\V1;

use App\Repository\V1\RolRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class RolService
{
    private $rolRepository;

    public function __construct(
        RolRepository $rolRepository
    ) {
        $this->rolRepository = $rolRepository;
    }

    public function index(Request $request): array
    {
        try {
            $paginatedData = $this->rolRepository->index($request);

            return [
                'code'   => Response::HTTP_OK,
                'result' => [
                    'data'        => $paginatedData->items(),
                    'total'       => $paginatedData->total(),
                    'perPage'     => $paginatedData->perPage(),
                    'currentPage' => $paginatedData->currentPage(),
                    'lastPage'    => $paginatedData->lastPage(),
                ],
            ];
        } catch (\Throwable $th) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $message    = $th->getMessage();

            return [
                'code'    => $statusCode,
                'message' => $message,
            ];
        }
    }

    public function getRoleWithPermissions(Role $role): Role
    {
        $role->load('permissions');

        return $role;
    }

    public function updateRolePermissions(Request $request, Role $role): array
    {
        try {
            $request->validate([
                'permissions'        => 'required|array',
                'permissions.*.name' => [
                    'required',
                    'string',
                    Rule::exists('permissions', 'name'),
                ],
            ]);

            $allPermissionsForRole = $request->input('permissions');
            $permissionNames       = array_map(function ($permission) {
                return $permission['name'];
            }, $allPermissionsForRole);

            // Sincronizar permisos
            $role->syncPermissions($permissionNames);

            return ['message' => 'Permissions updated successfully'];
        } catch (ValidationException $e) {
            return ['error' => $e->errors(), 'code' => 422]; // 422 Unprocessable Entity
        } catch (\Exception $e) {
            return ['error' => 'Error interno del servidor', 'code' => 500];
        }
    }

    public function storeRole(array $data): array
    {
        try {
            $role = Role::create([
                'name'       => $data['name'],
                'guard_name' => 'web',
            ]);
            $role->givePermissionTo($data['permissions']);

            return [
                'message' => 'Role created and permissions assigned successfully',
                'role'    => $role,
                'code'    => 201,
            ];
        } catch (\Exception $e) {
            return ['error' => 'Error interno del servidor', 'code' => 500];
        }
    }

    public function destroyRole(Role $role): array
    {
        try {
            if ($role->name === 'administrador') {
                return ['message' => 'No se puede eliminar el rol administrador.', 'code' => 400];
            }

            // Verificar si hay algún usuario activo con ese rol
            $usersWithRole = $role->users()->whereNull('users.deleted_at')->count();

            if ($usersWithRole > 0) {
                return ['message' => 'No se puede eliminar el rol. Hay usuarios activos asociados a ese rol.', 'code' => 400];
            }

            // Eliminar permisos asociados al rol
            foreach ($role->permissions as $permission) {
                $role->revokePermissionTo($permission);
            }

            // Eliminar el rol
            $role->delete();

            return ['message' => $role->name.' y permisos asociados al mismo eliminados satisfactoriamente.', 'code' => 200];
        } catch (\Exception $e) {
            return ['error' => 'Error interno del servidor', 'code' => 500];
        }
    }
}
