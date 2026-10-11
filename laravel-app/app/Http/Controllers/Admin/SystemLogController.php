<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{
    public function index(Request $request)
    {
        // Admin lang (session-based, gaya ng sidebar mo)
        abort_unless(
            in_array(session('role'), (array) config('system_log.admin_roles', ['admin']), true),
            403
        );

        $logs = SystemLog::query()
            ->when($request->filled('severity'), fn ($q) => $q->where('severity', $request->severity))
            ->when($request->filled('module'), fn ($q) => $q->where('module', $request->module))
            ->when($request->filled('event_type'), fn ($q) => $q->where('event_type', $request->event_type))
            ->when($request->boolean('anomaly_only'), fn ($q) => $q->where('is_anomaly', true))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = '%' . $request->search . '%';
                $q->where(fn ($w) => $w->where('email', 'like', $s)
                    ->orWhere('actor_name', 'like', $s)
                    ->orWhere('ip_address', 'like', $s)
                    ->orWhere('description', 'like', $s));
            })
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $since = now()->subDay();
        $summary = [
            'total' => SystemLog::where('created_at', '>=', $since)->count(),
            'anomalies' => SystemLog::where('created_at', '>=', $since)->where('is_anomaly', true)->count(),
            'critical' => SystemLog::where('created_at', '>=', $since)->where('severity', 'critical')->count(),
            'failed_logins' => SystemLog::where('created_at', '>=', $since)->where('event_type', 'login_failed')->count(),
        ];

        $eventTypes = SystemLog::select('event_type')->distinct()->orderBy('event_type')->pluck('event_type');
        $modules = SystemLog::whereNotNull('module')->select('module')->distinct()->orderBy('module')->pluck('module');

        return view('admin.system-logs.index', compact('logs', 'summary', 'eventTypes', 'modules'));
    }
}