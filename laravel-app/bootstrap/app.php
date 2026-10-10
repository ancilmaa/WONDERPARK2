<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        // Public ang QR check-in at protektado na ito ng QR token at PIN,
        // kaya hindi na kailangan ng CSRF dito (iwas "CSRF token mismatch"
        // kapag nag-expire ang session sa phone).
        $middleware->validateCsrfTokens(except: [
            'attendance/checkin',
        ]);

        $middleware->alias([
            'auth.session' => \App\Http\Middleware\EnsureUserIsLoggedIn::class,
        ]);

        // System Logs: nagde-detect ng kakaibang galaw (request spike, 403 / bawal na access).
        // Naka-append sa dulo ng "web" group para tapos na ang session bago ito tumakbo.
        $middleware->appendToGroup('web', \App\Http\Middleware\DetectUnusualActivity::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();