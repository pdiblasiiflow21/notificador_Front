<?php

declare(strict_types=1);

namespace App\QueryFilters;

use Closure;

class RoleId
{
    public function handle($request, Closure $next): \Illuminate\Database\Eloquent\Builder
    {
        $builder = $next($request);
        $roleId  = request()->get('roleId');

        if ($roleId) {
            $builder->whereHas('roles', function ($query) use ($roleId) {
                $query->where('id', $roleId);
            });
        }

        return $builder;
    }
}
