<?php

declare(strict_types=1);

namespace App\Repository\V1;

use App\Models\User;
use App\QueryFilters\RoleId;
use App\QueryFilters\Username;
use App\Repository\EloquentRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pipeline\Pipeline;

class UserRepository extends EloquentRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function index(Request $request): LengthAwarePaginator
    {
        $per_page    = $request->input('per_page', self::PER_PAGE);
        $currentPage = $request->input('current_page', 1);

        $builder = $this->applyFilters($request, ['users.*']);

        return $builder->paginate($per_page, ['*'], 'current_page', $currentPage);
    }

    private function applyFilters(Request $request, array $columns = []): Builder
    {
        $usersWithRoles = User::query()->withTrashed();

        if (! empty($columns)) {
            $usersWithRoles = $usersWithRoles->select($columns);
        }

        // defino un ordenamiento por defecto
        $column = ! $request->column ? 'id' : $request->column;

        // defino una direccion de ordenamiento por defecto
        $direction = ! $request->order_by ? 'desc' : $request->order_by;

        if ($direction === 'desc') {
            $usersWithRoles->orderByDesc($column);
        } else {
            $usersWithRoles->orderBy($column);
        }

        $usersWithRoles->with('roles');

        return app(Pipeline::class)
            ->send($usersWithRoles)
            ->through([
                Username::class,
                RoleId::class,
            ])
            ->thenReturn();
    }
}
