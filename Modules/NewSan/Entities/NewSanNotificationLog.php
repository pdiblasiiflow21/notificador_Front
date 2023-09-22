<?php

declare(strict_types=1);

namespace Modules\NewSan\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\NewSan\Database\factories\NewSanNotificationLogFactory;

class NewSanNotificationLog extends Model
{
    use HasFactory;

    protected $table = 'NewSan_notification_logs';

    protected static function newFactory()
    {
        return NewSanNotificationLogFactory::new();
    }

    protected $fillable = [
        'message',
        'notified',
        'finalized',
        'response_time',
    ];
}
