<?php

namespace Mixu\SSOAuth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccessArea
{
    /**
     * Cek apakah user punya salah satu access_area yang diizinkan.
     * Usage: ->middleware('access_area:portal,supervisor')
     */
    public function handle(Request $request, Closure $next, string ...$areas): Response
    {
        $user = $request->session()->get('sso_user');
        if (! $user || empty($user['access_areas']) || ! is_array($user['access_areas'])) {
            return $this->deny($request);
        }

        $allowed = array_map('strtolower', $areas);
        $userAreas = array_map('strtolower', $user['access_areas']);
        if (count(array_intersect($allowed, $userAreas)) === 0) {
            return $this->deny($request);
        }

        return $next($request);
    }

    protected function deny(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }
        abort(403, 'You do not have access to this area.');
        // return redirect()->route('forbidden')->with('message', __('You do not have access to this area.'));
    }
}
