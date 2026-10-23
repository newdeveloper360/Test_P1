<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class EnsureNotInMaintenance
{
    public function handle(Request $request, Closure $next): HttpResponse
    {
        if ($request->user()?->role !== 'admin' && Setting::current()->maintenance_mode)
            return Response::failed(Setting::current()->maintenance_message
                ?: 'The app is under maintenance. Please try again later.', null, 503);
        return $next($request);
    }
}
