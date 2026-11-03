<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_photographers_and_add_photographer(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('photographers.index'));
        $response->assertStatus(200);
        $response->assertSee('Photographers Directory');
        $response->assertSee('Add Photographer');

        $postResponse = $this->post(route('photographers.store'), [
            'name' => 'Karan Oberoi',
            'email' => 'karan@studio.com',
            'phone' => '9898989898',
            'specialization' => 'Candid Wedding Photographer',
            'password' => 'secret123',
        ]);

        $postResponse->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'name' => 'Karan Oberoi',
            'email' => 'karan@studio.com',
            'role' => 'photographer',
            'specialization' => 'Candid Wedding Photographer',
        ]);
    }

    public function test_admin_can_view_crew_and_add_telecaller(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('crew.index'));
        $response->assertStatus(200);
        $response->assertSee('Studio Crew', false);
        $response->assertSee('Add Crew Member', false);

        $postResponse = $this->post(route('crew.store'), [
            'name' => 'Pooja Verma',
            'email' => 'pooja@studio.com',
            'phone' => '9123456780',
            'role' => 'telecaller',
            'specialization' => 'Senior Client Support Executive',
            'password' => 'secret123',
        ]);

        $postResponse->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'name' => 'Pooja Verma',
            'email' => 'pooja@studio.com',
            'role' => 'telecaller',
            'specialization' => 'Senior Client Support Executive',
        ]);
    }

    public function test_admin_can_delete_crew_member(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $crewMember = User::factory()->create([
            'name' => 'Temporary Staff',
            'email' => 'temp@studio.com',
            'role' => 'telecaller',
        ]);

        $this->actingAs($admin);

        $response = $this->delete(route('crew.destroy', $crewMember));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', [
            'id' => $crewMember->id,
        ]);
    }

    public function test_telecaller_can_access_crew_page_and_add_crew(): void
    {
        $telecaller = User::factory()->create([
            'role' => 'telecaller',
        ]);

        $this->actingAs($telecaller);

        $response = $this->get(route('crew.index'));
        $response->assertStatus(200);

        $postResponse = $this->post(route('crew.store'), [
            'name' => 'Rohan Mehta',
            'email' => 'rohan@studio.com',
            'phone' => '9876543219',
            'role' => 'telecaller',
            'specialization' => 'Lead Telecaller',
        ]);

        $postResponse->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'name' => 'Rohan Mehta',
            'role' => 'telecaller',
        ]);
    }

    public function test_photographer_cannot_access_crew_management(): void
    {
        $photographer = User::factory()->create([
            'role' => 'photographer',
        ]);

        $this->actingAs($photographer);

        $response = $this->get(route('crew.index'));
        $response->assertRedirect(route('photographers.portal'));
        $response->assertSessionHas('error');
    }

    public function test_admin_can_search_and_filter_crew_table(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Boss']);
        $telecaller = User::factory()->create([
            'role' => 'telecaller',
            'name' => 'Meera Nair',
            'email' => 'meera@studio.com',
            'specialization' => 'Senior Telecaller',
        ]);
        $opsAdmin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Kabir Kapoor',
            'email' => 'kabir@studio.com',
            'specialization' => 'Studio Operations',
        ]);

        $this->actingAs($admin);

        // Search test
        $response = $this->get(route('crew.index', ['search' => 'Meera']));
        $response->assertStatus(200);
        $response->assertSee('Meera Nair', false);
        $response->assertDontSee('Kabir Kapoor', false);

        // Role filter test
        $roleResponse = $this->get(route('crew.index', ['role' => 'telecaller']));
        $roleResponse->assertStatus(200);
        $roleResponse->assertSee('Meera Nair', false);
        $roleResponse->assertDontSee('Kabir Kapoor', false);

        // Sort filter test
        $sortResponse = $this->get(route('crew.index', ['sort' => 'name_asc']));
        $sortResponse->assertStatus(200);
        $sortResponse->assertSee('crew-table', false);
    }

    public function test_admin_can_search_filter_and_delete_photographer(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $photographer1 = User::factory()->create([
            'role' => 'photographer',
            'name' => 'Aarav Drone Specialist',
            'email' => 'aarav@studio.com',
            'specialization' => 'Drone Operator & Aerial Specialist',
        ]);
        $photographer2 = User::factory()->create([
            'role' => 'photographer',
            'name' => 'Deepak Candid Shooter',
            'email' => 'deepak@studio.com',
            'specialization' => 'Candid Wedding Photographer',
        ]);

        $this->actingAs($admin);

        // Search test
        $response = $this->get(route('photographers.index', ['search' => 'Aarav']));
        $response->assertStatus(200);
        $response->assertSee('Aarav Drone Specialist', false);
        $response->assertDontSee('Deepak Candid Shooter', false);

        // Filter test
        $filterResponse = $this->get(route('photographers.index', ['specialization' => 'Drone Operator & Aerial Specialist']));
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('Aarav Drone Specialist', false);
        $filterResponse->assertDontSee('Deepak Candid Shooter', false);

        // Delete test
        $deleteResponse = $this->delete(route('photographers.destroy', $photographer1));
        $deleteResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $photographer1->id]);
    }
}
