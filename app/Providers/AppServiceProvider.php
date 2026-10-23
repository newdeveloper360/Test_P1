<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $phone = strtolower(trim((string) $request->input('phone', '')));
            return [Limit::perMinute(20)->by('login-ip|'.$request->ip()),
                Limit::perMinute(5)->by('login-phone|'.$request->ip().'|'.$phone)];
        });
        RateLimiter::for('registration', function (Request $request) {
            $phone = strtolower(trim((string) $request->input('phone', '')));
            return [Limit::perHour(10)->by('registration-ip|'.$request->ip()),
                Limit::perHour(3)->by('registration-phone|'.$request->ip().'|'.$phone)];
        });
        Response::macro('success', function ($message = null, $data = null, $statusCode = 200) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data
            ], $statusCode);
        });

        Response::macro('failed', function ($message = null, $errors = null, $statusCode = 400) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => $errors
            ], $statusCode);
        });
    }
}
