<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationAndRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_dashboard_or_studio_routes(): void
    {
        // Root dashboard cannot be accessed without login
        $response = $this->get('/');
        $response->assertRedirect(route('login'));

        // Protected studio routes cannot be accessed without login
        $response = $this->get(route('shoots.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('customers.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('leads.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('photographers.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_super_admin_can_log_in_and_is_redirected_to_dashboard(): void
    {
        $superAdmin = User::factory()->create([
            'email' => 'superadmin@shuttercode.com',
            'role' => 'super_admin',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'superadmin@shuttercode.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($superAdmin);
    }

    public function test_telecaller_can_log_in_and_is_redirected_to_dashboard(): void
    {
        $telecaller = User::factory()->create([
            'email' => 'neha@shuttercode.com',
            'role' => 'telecaller',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'neha@shuttercode.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($telecaller);
    }

    public function test_photographer_can_log_in_and_is_redirected_to_photographer_dashboard(): void
    {
        $photographer = User::factory()->create([
            'email' => 'arun@shuttercode.com',
            'role' => 'photographer',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'arun@shuttercode.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('photographers.dashboard'));
        $this->assertAuthenticatedAs($photographer);
    }

    public function test_quick_login_supports_multiple_roles_and_emails(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@shuttercode.com',
            'role' => 'admin',
        ]);

        $telecaller = User::factory()->create([
            'email' => 'neha@shuttercode.com',
            'role' => 'telecaller',
        ]);

        $photographer = User::factory()->create([
            'email' => 'arun@shuttercode.com',
            'role' => 'photographer',
        ]);

        // Quick sign in by email
        $response = $this->post(route('login.quick'), [
            'email' => 'admin@shuttercode.com',
        ]);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin);

        // Quick sign in as telecaller
        $response = $this->post(route('login.quick'), [
            'email' => 'neha@shuttercode.com',
        ]);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($telecaller);

        // Quick sign in as photographer redirects to photographer dashboard
        $response = $this->post(route('login.quick'), [
            'role' => 'photographer',
        ]);
        $response->assertRedirect(route('photographers.dashboard'));
        $this->assertAuthenticatedAs($photographer);
    }

    public function test_user_can_logout_and_is_redirected_to_login(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('logout'));
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
