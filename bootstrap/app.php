<?php

/*
| When config (or routes) are cached from a real .env (e.g. WAMP subdirectory URL),
| PHPUnit's per-test env vars are ignored and the suite breaks (404/419/wrong DB).
| Drop cached bootstrap files for the testing environment before the app boots.
*/
$appEnv = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? null);

if ($appEnv === 'testing') {
    $cacheDir = __DIR__.'/cache';

    foreach (['config.php', 'routes-v7.php', 'events.php'] as $cachedFile) {
        $path = $cacheDir.'/'.$cachedFile;

        if (is_file($path)) {
            unlink($path);
        }
    }
}

use App\Exceptions\StoreException;
use App\Http\Middleware\EnsureJsonRequest;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\AuthenticateSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);

        $middleware->append(SecurityHeaders::class);

        $middleware->web(append: [
            AuthenticateSession::class,
        ]);

        $middleware->api(prepend: [
            EnsureJsonRequest::class,
        ]);

        $middleware->api(append: [
            'throttle:api',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (StoreException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage())->withInput();
        });
    })->create();
