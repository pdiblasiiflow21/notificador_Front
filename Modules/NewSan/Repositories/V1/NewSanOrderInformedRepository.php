<?php

declare(strict_types=1);

namespace Modules\NewSan\Repositories\V1;

use App\Repository\EloquentRepository;
use Illuminate\Database\Eloquent\Collection;
use Modules\NewSan\Entities\NewSanOrderInformed;

class NewSanOrderInformedRepository extends EloquentRepository
{
    public function __construct(NewSanOrderInformed $model)
    {
        parent::__construct($model);
    }

    public function updateOrCreate(array $attributes, array $values): NewSanOrderInformed
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    public function getUnfinalizedOrders(): Collection
    {
        return $this->model->where('finalized', false)->get();
    }
}
