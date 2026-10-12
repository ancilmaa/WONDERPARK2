<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * System / activity log.
 *
 * SystemLog::record('booking.created', 'Created booking #12', null, 'booking', 12);
 *
 * Never throws: a logging problem must not break the user's request.
 * Never store passwords, codes, tokens or request bodies here.
 */
class SystemLog extends Model
{
    public $timestamps = false;

    protected $table = 'system_logs';

    protected $fillable = [
        'user_id', 'role', 'action', 'description',
        'subject_type', 'subject_id', 'ip_address', 'user_agent', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public static function record(
        string $action,
        string $description,
        ?int $userId = null,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?string $role = null
    ): void {
        try {
            $request = request();
            $sessionUser = session('user_id');

            DB::table('system_logs')->insert([
                'user_id'      => $userId ?? ($sessionUser !== null ? (int) $sessionUser : null),
                'role'         => $role ?? session('role'),
                'action'       => mb_substr($action, 0, 60),
                'description'  => mb_substr($description, 0, 255),
                'subject_type' => $subjectType,
                'subject_id'   => $subjectId,
                'ip_address'   => $request ? $request->ip() : null,
                'user_agent'   => $request ? mb_substr((string) $request->userAgent(), 0, 255) : null,
                'created_at'   => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('System log write failed: ' . $e->getMessage());
        }
    }
}
