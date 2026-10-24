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
        // The free Render deployment uses array caching; limits must persist between requests.
        if ($this->app->environment('production')) {
            config(['cache.limiter' => 'database']);
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
        RateLimiter::for('contact-form', fn (Request $request) => [
            Limit::perMinute(2)->by('contact-minute|'.$request->ip()),
            Limit::perHour(5)->by('contact-hour|'.$request->ip()),
        ]);
        RateLimiter::for('admin-panel', fn (Request $request) =>
            Limit::perMinute(120)->by('admin|'.($request->user()?->id ?: $request->ip())));
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
