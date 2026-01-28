<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    // newly added
   ->withMiddleware(function (Middleware $middleware) {
    $middleware->group('api', [
        //'throttle:api',
        \Illuminate\Http\Middleware\HandleCors::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ]);
    })
    ->create();
