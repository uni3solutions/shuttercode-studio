<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create([
            'role' => 'admin',
        ]));
    }

    public function test_can_view_packages_index(): void
    {
        Package::factory()->create([
            'name' => 'Royal Heritage Wedding Package',
            'price' => 175000,
        ]);

        $response = $this->get(route('packages.index'));

        $response->assertStatus(200);
        $response->assertSee('Royal Heritage Wedding Package');
        $response->assertSee(number_format(175000));
    }

    public function test_can_create_new_package(): void
    {
        $response = $this->post(route('packages.store'), [
            'name' => 'Pre-Wedding Sunset Story',
            'category' => 'Pre-Wedding',
            'price' => 55000,
            'advance_amount' => 15000,
            'duration_hours' => 6,
            'photographers_count' => 2,
            'videographers_count' => 1,
            'drone_included' => '1',
            'album_count' => 1,
            'deliverables' => '100 edited portraits, 60s Instagram Reel',
            'description' => 'Ideal for couples wanting cinematic sunset frames',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('packages.index'));
        $this->assertDatabaseHas('packages', [
            'name' => 'Pre-Wedding Sunset Story',
            'price' => 55000,
            'drone_included' => true,
        ]);
    }

    public function test_can_toggle_package_status(): void
    {
        $package = Package::factory()->create(['is_active' => true]);

        $response = $this->post(route('packages.toggle', $package));

        $response->assertRedirect();
        $this->assertFalse($package->fresh()->is_active);
    }

    public function test_packages_index_displays_table_format_with_telecaller_name(): void
    {
        $telecaller = User::factory()->create([
            'name' => 'Neha Sharma Telecaller',
            'role' => 'telecaller',
        ]);

        Package::factory()->create([
            'name' => 'Neha Luxury Wedding',
            'category' => 'Wedding',
            'created_by' => $telecaller->id,
            'price' => 120000,
            'advance_amount' => 40000,
        ]);

        $response = $this->get(route('packages.index'));

        $response->assertStatus(200);
        $response->assertSee('Neha Luxury Wedding');
        $response->assertSee('Neha Sharma Telecaller');
        $response->assertSee('Wedding');
        $response->assertSee('Table');
        $response->assertSee('Package & Deliverables');
        $response->assertSee('Telecaller / Owner');
    }

    public function test_packages_index_filters_by_search_query(): void
    {
        $pkg1 = Package::factory()->create(['name' => 'Alpha Majestic Royal', 'deliverables' => 'Drone 4K aerial shots']);
        $pkg2 = Package::factory()->create(['name' => 'Beta Simple Portraits', 'deliverables' => 'Single photographer']);

        $response = $this->get(route('packages.index', ['search' => 'Majestic']));
        $response->assertStatus(200);
        $response->assertSee('Alpha Majestic Royal');
        $response->assertDontSee('Beta Simple Portraits');
    }

    public function test_packages_index_filters_by_telecaller_and_scope(): void
    {
        $telecaller1 = User::factory()->create(['name' => 'Kiran Gowda', 'role' => 'telecaller']);
        $telecaller2 = User::factory()->create(['name' => 'Pooja Hegde', 'role' => 'telecaller']);

        $pkg1 = Package::factory()->create(['name' => 'Kiran Exclusive Combo', 'created_by' => $telecaller1->id]);
        $pkg2 = Package::factory()->create(['name' => 'Pooja Deluxe Package', 'created_by' => $telecaller2->id]);

        // Filter by telecaller 1
        $response = $this->get(route('packages.index', ['telecaller_id' => $telecaller1->id]));
        $response->assertStatus(200);
        $response->assertSee('Kiran Exclusive Combo');
        $response->assertDontSee('Pooja Deluxe Package');

        // Acting as telecaller 1 with scope=my
        $responseMy = $this->actingAs($telecaller1)->get(route('packages.index', ['scope' => 'my']));
        $responseMy->assertStatus(200);
        $responseMy->assertSee('Kiran Exclusive Combo');
        $responseMy->assertDontSee('Pooja Deluxe Package');
    }

    public function test_packages_index_filters_by_category_and_status(): void
    {
        $activeWedding = Package::factory()->create(['category' => 'Wedding', 'is_active' => true, 'name' => 'Active Wedding Tier']);
        $inactiveWedding = Package::factory()->create(['category' => 'Wedding', 'is_active' => false, 'name' => 'Inactive Wedding Tier']);
        $activeCorporate = Package::factory()->create(['category' => 'Corporate', 'is_active' => true, 'name' => 'Active Corporate Tier']);

        // Filter category = Wedding and status = active
        $response = $this->get(route('packages.index', ['category' => 'Wedding', 'status' => 'active']));
        $response->assertStatus(200);
        $response->assertSee('Active Wedding Tier');
        $response->assertDontSee('Inactive Wedding Tier');
        $response->assertDontSee('Active Corporate Tier');
    }

    public function test_packages_index_supports_pagination(): void
    {
        Package::factory()->count(15)->create();

        $response = $this->get(route('packages.index', ['per_page' => 10]));
        $response->assertStatus(200);
        $response->assertSee('Showing <span class="font-semibold text-slate-800">1</span> to <span class="font-semibold text-slate-800">10</span> of', false);
    }
}
