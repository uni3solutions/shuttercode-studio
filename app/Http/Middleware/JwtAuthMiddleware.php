<?php

namespace App\Http\Middleware;

use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthMiddleware
{
    public function __construct(protected JwtService $jwtService) {}

    /**
     * Handle an incoming request authenticated via JWT.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken()
            ?: $request->query('jwt')
            ?: $request->cookie('jwt_token');

        if (! $token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated: Missing JWT Bearer token.',
            ], 401);
        }

        $user = $this->jwtService->getUserFromToken($token);

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated: Invalid or expired JWT token.',
            ], 401);
        }

        // Authenticate user in Laravel context for this request
        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
