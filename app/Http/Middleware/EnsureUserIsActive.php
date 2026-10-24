<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Response as ApiResponse;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role === 'admin') {
            return ApiResponse::failed('Admin accounts use the Laravel website.', null, 403);
        }
        if ($request->user()?->is_blocked) {
            return ApiResponse::failed('This account is blocked.', null, 403);
        }

        if (!$request->user()?->is_active) {
            $request->user()?->tokens()->delete();
            return ApiResponse::failed('This account is inactive. Contact support.', null, 403);
        }
        return $next($request);
    }
}
