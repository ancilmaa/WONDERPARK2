<?php

namespace App\Http\Middleware;

use App\Services\SystemLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class DetectUnusualActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        // 1) Request spike: bilangin ang request per minute per IP
        $key = 'syslog:rpm:' . $ip . ':' . now()->format('YmdHi');
        Cache::add($key, 0, 90);
        $count = Cache::increment($key);
        $threshold = (int) config('system_log.requests_per_minute_threshold', 120);

        if ($count > $threshold && Cache::add($key . ':flagged', 1, 90)) {
            SystemLogger::anomaly(
                'request_spike',
                "Sobrang daming request ({$count}/min) mula sa IP {$ip}.",
                'warning',
                ['requests_per_minute' => $count, 'threshold' => $threshold]
            );
        }

        $response = $next($request);

        // 2) Pagtatangkang mag-access ng bawal (403)
        if ($response->getStatusCode() === 403) {
            SystemLogger::anomaly(
                'unauthorized_access',
                'Bawal na access: ' . $request->method() . ' /' . ltrim($request->path(), '/'),
                'warning',
                ['status' => 403]
            );
        }

        return $response;
    }
}
