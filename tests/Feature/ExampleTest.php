<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Package;
use App\Models\Shoot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create([
            'role' => 'admin',
        ]));
    }

    public function test_the_dashboard_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('ShutterCode');
    }

    public function test_all_studio_screens_render_successfully(): void
    {
        $customer = Customer::factory()->create();
        $package = Package::factory()->create();
        $shoot = Shoot::factory()->create([
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'shoot_number' => 'SHT-2026-001',
        ]);

        $routes = [
            route('shoots.index'),
            route('shoots.create'),
            route('shoots.show', $shoot),
            route('shoots.edit', $shoot),
            route('customers.index'),
            route('customers.create'),
            route('customers.show', $customer),
            route('customers.edit', $customer),
            route('leads.index'),
            route('leads.create'),
            route('packages.index'),
            route('packages.create'),
            route('packages.edit', $package),
            route('photographers.index'),
            route('photographers.portal'),
            route('payments.index'),
            route('payments.create'),
            route('invoices.index'),
            route('invoices.create'),
            route('reports.index'),
            route('portal.gallery', 'SHT-2026-001'),
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertStatus(200);
        }
    }
}
