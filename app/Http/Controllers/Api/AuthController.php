<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(protected JwtService $jwtService) {}

    /**
     * API Login returning a signed JWT Bearer Token.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'The provided credentials do not match our records.',
            ], 401);
        }

        $ttl = 86400; // 24 hours
        $token = $this->jwtService->generateToken($user, $ttl);

        ActivityLog::log('jwt_api_login', "User {$user->name} authenticated via JWT API.", $user);

        return response()->json([
            'status' => 'success',
            'message' => 'Authenticated successfully.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $ttl,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'specialization' => $user->specialization,
            ],
        ]);
    }

    /**
     * Get currently authenticated user profile via JWT.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'specialization' => $user->specialization,
                'created_at' => $user->created_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Refresh an existing JWT token.
     */
    public function refresh(Request $request): JsonResponse
    {
        $token = $request->bearerToken() ?: $request->input('token');

        if (! $token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token not provided.',
            ], 400);
        }

        $newToken = $this->jwtService->refreshToken($token);

        if (! $newToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token is invalid or cannot be refreshed.',
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'access_token' => $newToken,
            'token_type' => 'Bearer',
            'expires_in' => 86400,
        ]);
    }

    /**
     * Logout and end API session.
     */
    public function logout(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully.',
        ]);
    }
}
