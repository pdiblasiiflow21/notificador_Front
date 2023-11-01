<?php

declare(strict_types=1);

namespace App\Service\V1;

use App\Models\User;
use App\Repository\V1\UserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Role;

class UserService
{
    private $userRepository;

    public function __construct(
        UserRepository $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    public function index(Request $request)
    {
        try {
            $paginatedData = $this->userRepository->index($request);

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

    public function store(array $data): array
    {
        try {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => bcrypt($data['password']),
            ]);

            $role = Role::findOrFail($data['role']);
            $user->assignRole($role);

            return [
                'code'    => Response::HTTP_CREATED,
                'message' => 'Usuario creado con éxito!',
                'user'    => $user,
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'code'    => Response::HTTP_NOT_FOUND,
                'message' => 'Rol no encontrado',
            ];
        } catch (\Exception $e) {
            return [
                'code'    => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Error interno del servidor',
            ];
        }
    }

    public function update(User $user, array $data): array
    {
        try {
            if (isset($data['password'])) {
                $data['password'] = bcrypt($data['password']);
            }

            $user->update($data);

            if (isset($data['role'])) {
                $role = Role::findOrFail($data['role']);
                $user->syncRoles([$role->name]);
            }

            return [
                'code'    => Response::HTTP_OK,
                'message' => 'Usuario actualizado con éxito!',
                'user'    => $user,
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'code'    => Response::HTTP_NOT_FOUND,
                'message' => 'Usuario o Rol no encontrado',
            ];
        } catch (\Exception $e) {
            return [
                'code'    => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Error interno del servidor',
            ];
        }
    }

    public function toggleStatus(int $id): array
    {
        try {
            $user      = User::withTrashed()->findOrFail($id);
            $wasActive = ($user->deleted_at === null);

            if ($user->hasRole('administrador') && $user->id === 1 && $wasActive) {
                return [
                    'code'    => Response::HTTP_FORBIDDEN,
                    'message' => 'No se puede desactivar al usuario administrador',
                ];
            }

            if ($wasActive) {
                $user->delete();
            } else {
                $user->restore();
            }

            $user->load('roles');
            $message = $wasActive
                ? "El usuario $user->name se desactivó correctamente."
                : "El usuario $user->name se activó correctamente.";

            return [
                'code'    => Response::HTTP_OK,
                'message' => $message,
                'user'    => $user,
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'code'    => Response::HTTP_NOT_FOUND,
                'message' => 'Usuario no encontrado',
            ];
        } catch (\Exception $e) {
            return [
                'code'    => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Error interno del servidor',
            ];
        }
    }
}
