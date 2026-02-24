<?php

namespace Mixu\SSOAuth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Cek apakah user punya salah satu role yang diizinkan.
     * Usage: ->middleware('role:admin,super_admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->session()->get('sso_user');
        if (! $user || empty($user['roles']) || ! is_array($user['roles'])) {
            return $this->deny($request);
        }

        $allowed = array_map('strtolower', $roles);
        $userRoles = array_map('strtolower', $user['roles']);
        if (count(array_intersect($allowed, $userRoles)) === 0) {
            return $this->deny($request);
        }

        return $next($request);
    }

    protected function deny(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }
        abort(403, 'You do not have permission to access this page.');
        // return redirect()->route('forbidden')->with('message', __('You do not have permission to access this page.'));
    }
}
