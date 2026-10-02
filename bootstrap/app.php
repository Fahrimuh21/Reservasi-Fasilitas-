<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Daftarkan alias middleware RoleMiddleware sebagai 'role'
        // Sesuai DESIGN.md Bagian 19 & PRD Implementation Plan Fase 2
        // Penggunaan: Route::middleware(['auth:sanctum', 'role:admin'])
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('api/*') ? null : '/login');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Endpoint API selalu mengembalikan JSON, termasuk request file dari
        // browser yang tidak otomatis mengirim header Accept: application/json.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $exception) => $request->is('api/*') || $request->expectsJson()
        );
    })->create();
