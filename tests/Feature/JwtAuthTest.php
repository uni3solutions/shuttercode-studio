<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class JwtAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_generates_valid_jwt_token(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@shuttercode.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $response = $this->postJson(route('api.login'), [
            'email' => 'admin@shuttercode.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'access_token',
                'token_type',
                'expires_in',
                'user' => ['id', 'name', 'email', 'role'],
            ]);

        $token = $response->json('access_token');
        $this->assertNotEmpty($token);

        // Verify token with JwtService
        $jwtService = app(JwtService::class);
        $payload = $jwtService->validateToken($token);

        $this->assertNotNull($payload);
        $this->assertEquals($user->id, $payload['sub']);
        $this->assertEquals('admin', $payload['role']);
    }

    public function test_api_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@shuttercode.com',
            'password' => Hash::make('correct_password'),
        ]);

        $response = $this->postJson(route('api.login'), [
            'email' => 'admin@shuttercode.com',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'The provided credentials do not match our records.',
            ]);
    }

    public function test_protected_route_rejects_requests_without_jwt(): void
    {
        $response = $this->getJson(route('api.me'));

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Unauthenticated: Missing JWT Bearer token.',
            ]);
    }

    public function test_protected_route_accepts_valid_jwt_bearer_token(): void
    {
        $user = User::factory()->create([
            'name' => 'Sneha Kapoor',
            'email' => 'sneha@shuttercode.com',
            'role' => 'telecaller',
        ]);

        $jwtService = app(JwtService::class);
        $token = $jwtService->generateToken($user);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.me'));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'user' => [
                    'id' => $user->id,
                    'name' => 'Sneha Kapoor',
                    'email' => 'sneha@shuttercode.com',
                    'role' => 'telecaller',
                ],
            ]);
    }

    public function test_jwt_token_refresh(): void
    {
        $user = User::factory()->create([
            'email' => 'photographer@shuttercode.com',
            'role' => 'photographer',
        ]);

        $jwtService = app(JwtService::class);
        $token = $jwtService->generateToken($user);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(route('api.refresh'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'access_token',
                'token_type',
                'expires_in',
            ]);

        $newToken = $response->json('access_token');
        $this->assertNotEmpty($newToken);

        $payload = $jwtService->validateToken($newToken);
        $this->assertEquals($user->id, $payload['sub']);
    }

    public function test_web_login_sets_jwt_token_in_session(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@shuttercode.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'admin@shuttercode.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->assertNotEmpty(session('jwt_token'));

        $jwtService = app(JwtService::class);
        $payload = $jwtService->validateToken(session('jwt_token'));
        $this->assertEquals($user->id, $payload['sub']);
    }
}
