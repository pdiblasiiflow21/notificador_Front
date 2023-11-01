<?php

declare(strict_types=1);

namespace App\QueryFilters;

use Closure;

class ModuleId
{
    public function handle($request, Closure $next): \Illuminate\Database\Eloquent\Builder
    {
        $builder  = $next($request);
        $moduleId = request()->get('moduleId');

        if (request()->has('moduleId')) {
            $moduleId = request()->get('moduleId');

            return $builder->whereHas('permissions', function ($query) use ($moduleId) {
                $query->where('name', 'like', $moduleId.'%');
            });
        }

        return $builder;
    }
}
