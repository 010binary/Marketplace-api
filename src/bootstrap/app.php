<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . "/../routes/web.php",
        commands: __DIR__ . "/../routes/console.php",
        health: "/up",
        then: function () {
            Route::middleware("api")
                ->prefix("api/v1")
                ->group(base_path("routes/api/v1.php"));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(
            prepend: [
                \App\Http\Middleware\ForceJsonResponse::class,
                \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            ],
        );

        $middleware->alias([
            "verified" =>
                \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            "role" => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (
            \Illuminate\Validation\ValidationException $e,
        ) {
            return response()->json(
                [
                    "message" => "Validation failed",
                    "errors" => $e->errors(),
                ],
                400,
            );
        });
    })
    ->create();
