<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        // If photographer tries to access unauthorized admin routes, redirect to their portal
        if ($user->isPhotographer()) {
            return redirect()->route('photographers.portal')
                ->with('error', 'Access restricted. You only have access to your assigned shoots workspace.');
        }

        abort(403, 'Unauthorized access.');
    }
}
