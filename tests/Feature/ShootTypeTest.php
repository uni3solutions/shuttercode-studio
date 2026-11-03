<?php

namespace Tests\Feature;

use App\Models\ShootType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShootTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_shoot_types_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ShootType::create(['name' => 'Baby Shoot', 'slug' => 'baby-shoot', 'is_active' => true]);

        $response = $this->actingAs($admin)->get(route('shoot-types.index'));

        $response->assertOk();
        $response->assertSee('Shoot & Event Types');
        $response->assertSee('Baby Shoot');
    }

    public function test_admin_can_create_new_shoot_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('shoot-types.store'), [
            'name' => 'Maternity Shoot',
            'description' => 'Maternity and pregnancy glow session',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('shoot_types', [
            'name' => 'Maternity Shoot',
            'slug' => 'maternity-shoot',
            'created_by' => $admin->id,
        ]);
    }

    public function test_admin_can_toggle_and_update_shoot_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $shootType = ShootType::create([
            'name' => 'Corporate Shoot',
            'slug' => 'corporate-shoot',
            'is_active' => true,
        ]);

        // Toggle status
        $responseToggle = $this->actingAs($admin)->post(route('shoot-types.toggle', $shootType));
        $responseToggle->assertRedirect();
        $this->assertFalse($shootType->fresh()->is_active);

        // Update name
        $responseUpdate = $this->actingAs($admin)->put(route('shoot-types.update', $shootType), [
            'name' => 'Corporate & Business Summit',
            'description' => 'Executive headshots and summit event',
            'is_active' => true,
        ]);
        $responseUpdate->assertRedirect();
        $this->assertEquals('Corporate & Business Summit', $shootType->fresh()->name);
    }

    public function test_telecaller_cannot_access_or_manage_shoot_types(): void
    {
        $telecaller = User::factory()->create(['role' => 'telecaller']);
        $shootType = ShootType::create([
            'name' => 'Baby Shoot',
            'slug' => 'baby-shoot',
            'is_active' => true,
        ]);

        // Access index -> 403
        $this->actingAs($telecaller)->get(route('shoot-types.index'))->assertForbidden();

        // Create -> 403
        $this->actingAs($telecaller)->post(route('shoot-types.store'), [
            'name' => 'Illegal Type',
        ])->assertForbidden();

        // Update -> 403
        $this->actingAs($telecaller)->put(route('shoot-types.update', $shootType), [
            'name' => 'Illegal Update',
        ])->assertForbidden();

        // Delete -> 403
        $this->actingAs($telecaller)->delete(route('shoot-types.destroy', $shootType))->assertForbidden();
    }

    public function test_leads_create_page_renders_event_type_select_with_shoot_types(): void
    {
        $telecaller = User::factory()->create(['role' => 'telecaller']);
        ShootType::create(['name' => 'Baby Shoot', 'slug' => 'baby-shoot', 'is_active' => true]);
        ShootType::create(['name' => 'Maternity Shoot', 'slug' => 'maternity-shoot', 'is_active' => true]);
        ShootType::create(['name' => 'Corporate Shoot', 'slug' => 'corporate-shoot', 'is_active' => true]);

        $response = $this->actingAs($telecaller)->get(route('leads.create'));

        $response->assertOk();
        // Check select element is present for event_type
        $response->assertSee('name="event_type"', false);
        $response->assertSee('Baby Shoot');
        $response->assertSee('Maternity Shoot');
        $response->assertSee('Corporate Shoot');
    }

    public function test_telecaller_can_create_lead_with_selected_shoot_type(): void
    {
        $telecaller = User::factory()->create(['role' => 'telecaller']);
        ShootType::create(['name' => 'Baby Shoot', 'slug' => 'baby-shoot', 'is_active' => true]);

        $response = $this->actingAs($telecaller)->post(route('leads.store'), [
            'customer_name' => 'Ananya Sharma',
            'phone' => '+91 98450 99999',
            'email' => 'ananya@example.com',
            'event_type' => 'Baby Shoot',
            'event_date' => '2026-11-20',
            'venue' => 'Whitefield Studio, Bengaluru',
            'estimated_budget' => 35000,
            'source' => 'WhatsApp Meta',
            'status' => 'new',
            'notes' => 'Looking for newborn 1st month photoshoot',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leads', [
            'customer_name' => 'Ananya Sharma',
            'event_type' => 'Baby Shoot',
            'created_by' => $telecaller->id,
        ]);
    }
}
