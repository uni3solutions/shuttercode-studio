<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Shoot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalSearchAndNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_search_returns_categorized_matches(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $customer = Customer::factory()->create([
            'name' => 'Vikram Singhania',
            'phone' => '9811223344',
            'email' => 'vikram@example.com',
        ]);

        $shoot = Shoot::factory()->create([
            'title' => 'Vikram Grand Wedding Reception',
            'customer_id' => $customer->id,
            'shoot_date' => now()->toDateString(),
        ]);

        $lead = Lead::factory()->create([
            'customer_name' => 'Vikram Malhotra',
            'phone' => '9988776655',
            'event_type' => 'Pre-wedding Shoot',
            'status' => 'new',
        ]);

        $photographer = User::factory()->create([
            'name' => 'Vikram Sharma',
            'role' => 'photographer',
            'specialization' => 'Lead Cinematographer',
        ]);

        $this->actingAs($admin);

        $response = $this->getJson(route('search.global', ['q' => 'Vikram']));

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertGreaterThanOrEqual(1, count($data['results']['shoots']));
        $this->assertGreaterThanOrEqual(1, count($data['results']['customers']));
        $this->assertGreaterThanOrEqual(1, count($data['results']['leads']));
        $this->assertGreaterThanOrEqual(1, count($data['results']['team']));

        $this->assertEquals('Vikram Singhania', $data['results']['customers'][0]['title']);
    }

    public function test_global_search_returns_empty_when_query_is_too_short(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->getJson(route('search.global', ['q' => 'a']));
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals(0, $data['total']);
    }

    public function test_topbar_renders_active_notifications_for_new_leads_and_today_shoots(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $customer = Customer::factory()->create(['name' => 'Sunil Verma']);
        Shoot::factory()->create([
            'title' => 'Destination Wedding',
            'customer_id' => $customer->id,
            'shoot_date' => now()->toDateString(),
        ]);

        Lead::factory()->create([
            'customer_name' => 'Kavita Chawla',
            'event_type' => 'Engagement Shoot',
            'status' => 'new',
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Studio Notifications', false);
        $response->assertSee('Destination Wedding', false);
        $response->assertSee('Kavita Chawla', false);
    }
}
