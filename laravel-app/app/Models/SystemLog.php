<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'event_type', 'severity', 'module', 'user_id', 'email', 'ip_address', 'user_agent',
        'method', 'url', 'description', 'context', 'is_anomaly',
    ];

    protected $casts = [
        'context' => 'array',
        'is_anomaly' => 'boolean',
        'created_at' => 'datetime',
    ];
}
