<?php

declare(strict_types=1);

namespace App\Repository\V1;

use App\Models\User;
use App\QueryFilters\ModuleId;
use App\Repository\EloquentRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pipeline\Pipeline;
use Spatie\Permission\Models\Role;

class RolRepository extends EloquentRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function index(Request $request): LengthAwarePaginator
    {
        $per_page    = $request->input('per_page', self::PER_PAGE);
        $currentPage = $request->input('current_page', 1);

        $builder = $this->applyFilters($request, ['roles.*']);

        return $builder->paginate($per_page, ['*'], 'current_page', $currentPage);
    }

    private function applyFilters(Request $request, array $columns = []): Builder
    {
        $rolesWithPermissions = Role::query();

        if (! empty($columns)) {
            $rolesWithPermissions = $rolesWithPermissions->select($columns);
        }

        // defino un ordenamiento por defecto
        $column = ! $request->column ? 'id' : $request->column;

        // defino una direccion de ordenamiento por defecto
        $direction = ! $request->order_by ? 'desc' : $request->order_by;

        if ($direction === 'desc') {
            $rolesWithPermissions->orderByDesc($column);
        } else {
            $rolesWithPermissions->orderBy($column);
        }

        $rolesWithPermissions->with('permissions');

        return app(Pipeline::class)
            ->send($rolesWithPermissions)
            ->through([
                // Username::class,
                ModuleId::class,
            ])
            ->thenReturn();
    }
}
