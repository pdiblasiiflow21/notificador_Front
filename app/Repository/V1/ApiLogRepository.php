<?php

declare(strict_types=1);

namespace App\Repository\V1;

use App\Models\ApiLog;
use App\Repository\EloquentRepository;

class ApiLogRepository extends EloquentRepository
{
    public function __construct()
    {
        parent::__construct(new ApiLog());
    }
}
