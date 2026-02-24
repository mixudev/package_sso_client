<?php

namespace Mixu\SSOAuth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSSOAuthenticated
{
    /**
     * Handle an incoming request.
     * User harus sudah login via SSO (session punya sso_user).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('sso_user')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login')->with('intended', $request->url());
        }

        return $next($request);
    }
}
