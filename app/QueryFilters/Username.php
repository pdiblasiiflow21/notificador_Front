<?php

declare(strict_types=1);

namespace App\QueryFilters;

use Closure;

class Username
{
    public function handle($request, Closure $next): \Illuminate\Database\Eloquent\Builder
    {
        $builder = $next($request);
        $name    = request()->get('name');

        if ($name) {
            $builder->where('name', 'LIKE', '%'.$name.'%');
        }

        return $builder;
    }
}
