<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;

return Application::configure(basePath: dirname(__DIR__))

    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
    api: __DIR__ . '/../routes/api.php', // ✅ aktifkan API route
    commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {

    // Redirect guest ke login
    $middleware->redirectTo(
            guests: '/login'
        );

    // Alias middleware (Laravel 11/12)
    $middleware->alias([
            'admin' => AdminMiddleware::class,
        ]);

    // Disable CSRF untuk route tertentu
    $middleware->validateCsrfTokens(except: [
        'api-test/enrollments',
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {
    // Optional: custom exception handling
})

    ->registered(function ($app) {
        // Untuk hosting cPanel (public_html)
        $app->usePublicPath(base_path('../public'));
    })

    ->create();
