<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_endpoint',
        'request_method',
        'request_credentials',
        'response_status_code',
        'response',
        'response_time',
    ];
}
