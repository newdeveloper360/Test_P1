<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureNotInMaintenance;
use App\Http\Middleware\EnsureAdminWeb;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias(['active.user' => EnsureUserIsActive::class,
            'app.maintenance' => EnsureNotInMaintenance::class,
            'admin.web' => EnsureAdminWeb::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        if (request()->is('api/*')) {
            $exceptions->render(function (AuthenticationException $e) {
                return Response::failed($e->getMessage(), null, 401);
            });

            $exceptions->render(function (ValidationException $e) {
                return Response::failed($e->getMessage(), $e->errors(), 422);
            });

            $exceptions->render(function (ThrottleRequestsException $e) {
                return Response::failed($e->getMessage(), null, 429);
            });

            $exceptions->render(function (HttpException $e) {
                return Response::failed($e->getMessage() ?: 'Request failed.', null, $e->getStatusCode());
            });
        }
    })->create();
