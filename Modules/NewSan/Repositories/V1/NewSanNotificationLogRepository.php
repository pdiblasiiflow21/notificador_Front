<?php

declare(strict_types=1);

namespace Modules\NewSan\Repositories\V1;

use App\Repository\EloquentRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pipeline\Pipeline;
use Modules\NewSan\Entities\NewSanNotificationLog;

class NewSanNotificationLogRepository extends EloquentRepository
{
    public function __construct(NewSanNotificationLog $model)
    {
        parent::__construct($model);
    }

    public function notificationLogs(Request $request): LengthAwarePaginator
    {
        $per_page = ! $request->per_page ? self::PER_PAGE : $request->per_page;

        $builder = $this->aplicarFiltrosBusqueda($request, ['NewSan_notification_logs.*']);

        return $builder->paginate($per_page);
    }

    private function aplicarFiltrosBusqueda(Request $request, array $columns = []): \Illuminate\Database\Eloquent\Builder
    {
        $newSanNotificationLogQuery = NewSanNotificationLog::query();

        if (! empty($columns)) {
            $newSanNotificationLogQuery = $newSanNotificationLogQuery->select($columns);
        }

        // defino un ordenamiento por defecto
        $column = ! $request->column ? 'id' : $request->column;

        // defino una direccion de ordenamiento por defecto
        $direction = ! $request->order_by ? 'desc' : $request->order_by;

        if ($direction === 'desc') {
            $newSanNotificationLogQuery->orderByDesc($column);
        } else {
            $newSanNotificationLogQuery->orderBy($column);
        }

        return app(Pipeline::class)
            ->send($newSanNotificationLogQuery)
            ->through([])
            ->thenReturn();
    }
}
