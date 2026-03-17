<?php

use App\Http\Middleware\EnsureDeviceSession;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'device.session' => EnsureDeviceSession::class,
        ]);
    })
    ->create();
