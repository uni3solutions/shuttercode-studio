<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an authenticated admin user
        $this->actingAs(User::factory()->create([
            'role' => 'admin',
        ]));
    }

    public function test_can_view_customers_index_page(): void
    {
        Customer::factory()->create([
            'name' => 'Aarav Patel',
            'phone' => '+91 98765 43210',
            'city' => 'Bengaluru',
        ]);

        $response = $this->get(route('customers.index'));

        $response->assertStatus(200);
        $response->assertSee('Aarav Patel');
        $response->assertSee('+91 98765 43210');
    }

    public function test_can_create_a_new_customer(): void
    {
        $response = $this->post(route('customers.store'), [
            'name' => 'Rohan & Ananya',
            'phone' => '+91 99887 76655',
            'email' => 'rohan@example.com',
            'city' => 'Mumbai',
            'address' => 'Bandra West, Mumbai',
            'notes' => 'Looking for cinematic candid photography',
        ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'Rohan & Ananya',
            'phone' => '+91 99887 76655',
            'city' => 'Mumbai',
        ]);

        $customer = Customer::where('phone', '+91 99887 76655')->first();
        $response->assertRedirect(route('customers.show', $customer));
    }

    public function test_can_update_customer_details(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Original Name',
            'phone' => '+91 91111 22222',
        ]);

        $response = $this->put(route('customers.update', $customer), [
            'name' => 'Updated Name',
            'phone' => '+91 91111 22222',
            'email' => 'updated@example.com',
            'city' => 'Bengaluru',
        ]);

        $response->assertRedirect(route('customers.show', $customer));
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Name',
        ]);
    }
}
