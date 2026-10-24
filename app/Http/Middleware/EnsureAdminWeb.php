<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminWeb
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();
        if (!$user) return redirect()->route('admin.login');
        if ($user->role !== 'admin' || $user->is_blocked || !$user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('admin.login')->withErrors([
                'phone' => 'Admin access is unavailable for this account.',
            ]);
        }
        if ($request->session()->get('require_password_update')
                && !$request->routeIs('admin.profile', 'admin.profile.update', 'admin.logout')) {
            return redirect()->route('admin.profile')->with('success', 'Set a 6-digit password before continuing.');
        }
        return $next($request);
    }
}
