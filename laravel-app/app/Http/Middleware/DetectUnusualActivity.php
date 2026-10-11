<?php

namespace App\Http\Middleware;

use App\Services\SystemLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Nagla-log ng 'unauthorized_access' kapag may tinanggihang request
 * (ang EnsureUserHasRole ang nagse-set ng 'access_denied' sa request attributes).
 *
 * Dapat ang middleware na ito ay NASA LABAS ng 'auth.session' at 'role:...'
 * (ibig sabihin, mas nauna sa pipeline) para mabasa nito ang attributes pagkatapos.
 * Halimbawa: i-append sa 'web' group sa bootstrap/app.php.
 */
class DetectUnusualActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
        } catch (HttpExceptionInterface $e) {
            // Ang abort(403) sa EnsureUserHasRole ay nagta-throw, kaya dito natin ito sasalo.
            $this->logIfDenied($request);

            throw $e;
        }

        // Para sa JSON 403 response (hindi nagta-throw)
        $this->logIfDenied($request);

        return $response;
    }

    private function logIfDenied(Request $request): void
    {
        $denied = $request->attributes->get('access_denied');

        if (!$denied) {
            return;
        }

        try {
            $user = $request->attributes->get('auth_user');
            $required = implode(', ', $denied['required'] ?? []);

            SystemLogger::record(
                'unauthorized_access',
                "Walang pahintulot ang user #{$denied['user_id']} ({$denied['role']}) sa {$request->method()} /{$request->path()} (kailangan: {$required})",
                'warning',
                [
                    'user_id'  => $denied['user_id'],
                    'role'     => $denied['role'],
                    'required' => $denied['required'] ?? [],
                    'method'   => $request->method(),
                    'path'     => '/' . ltrim($request->path(), '/'),
                    'ip'       => $request->ip(),
                ],
                false,
                $denied['user_id'],
                $user->email ?? null,
                'security'
            );
        } catch (\Throwable $e) {
            // Hindi dapat ma-block ang request dahil lang pumalya ang pag-log.
            Log::error('SystemLogger failed (unauthorized_access): ' . $e->getMessage());
        }

        // Iwas doble-log kung dalawang beses tinawag
        $request->attributes->remove('access_denied');
    }
}