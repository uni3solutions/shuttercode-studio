<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Package;
use App\Models\Shoot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelecallerPackageAndLeadTest extends TestCase
{
    use RefreshDatabase;

    public function test_telecaller_creates_lead_records_creator(): void
    {
        $telecaller = User::factory()->create([
            'name' => 'Kiran Telecaller',
            'role' => 'telecaller',
        ]);

        $this->actingAs($telecaller);

        $response = $this->post(route('leads.store'), [
            'customer_name' => 'Rahul & Priya',
            'phone' => '+91 98888 12345',
            'email' => 'rahul.priya@example.com',
            'event_type' => 'Wedding',
            'source' => 'WhatsApp Meta',
            'status' => 'new',
            'venue' => 'Palace Grounds',
            'estimated_budget' => 150000,
            'notes' => 'Enquired about wedding package',
        ]);

        $lead = Lead::where('phone', '+91 98888 12345')->first();
        $this->assertNotNull($lead);
        $this->assertEquals($telecaller->id, $lead->created_by);
        $this->assertEquals('Kiran Telecaller', $lead->creator->name);

        $response->assertRedirect(route('leads.show', $lead));

        // In leads index, creator is visible
        $indexResponse = $this->get(route('leads.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Kiran Telecaller');
        $indexResponse->assertSee('Rahul &amp; Priya', false);

        // In leads show, creator is visible
        $showResponse = $this->get(route('leads.show', $lead));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Kiran Telecaller');
    }

    public function test_telecaller_creates_package_records_creator(): void
    {
        $telecaller = User::factory()->create([
            'name' => 'Neha Telecaller',
            'role' => 'telecaller',
        ]);

        $this->actingAs($telecaller);

        $response = $this->post(route('packages.store'), [
            'name' => 'Neha Special Pre-Wedding',
            'category' => 'Pre-Wedding',
            'price' => 50000,
            'advance_amount' => 15000,
            'duration_hours' => 6,
            'photographers_count' => 1,
            'videographers_count' => 1,
            'drone_included' => true,
            'album_count' => 1,
            'deliverables' => 'Full HD video teaser, 150 edited photos',
            'description' => 'Exclusive telecaller package for destination pre-weddings',
        ]);

        $package = Package::where('name', 'Neha Special Pre-Wedding')->first();
        $this->assertNotNull($package);
        $this->assertEquals($telecaller->id, $package->created_by);
        $this->assertEquals('Neha Telecaller', $package->creator->name);

        $response->assertRedirect(route('packages.index'));

        // In packages index, creator is visible
        $indexResponse = $this->get(route('packages.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Neha Special Pre-Wedding');
        $indexResponse->assertSee('Created by You');
    }

    public function test_telecaller_sees_only_own_packages_when_creating_shoot(): void
    {
        $telecallerA = User::factory()->create([
            'name' => 'Telecaller Alice',
            'role' => 'telecaller',
        ]);

        $telecallerB = User::factory()->create([
            'name' => 'Telecaller Bob',
            'role' => 'telecaller',
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin Alex',
            'role' => 'admin',
        ]);

        $packageA = Package::factory()->create([
            'name' => 'Alice Luxury Wedding Package',
            'created_by' => $telecallerA->id,
            'is_active' => true,
        ]);

        $packageB = Package::factory()->create([
            'name' => 'Bob Budget Birthday Package',
            'created_by' => $telecallerB->id,
            'is_active' => true,
        ]);

        $packageAdmin = Package::factory()->create([
            'name' => 'Studio Default Premium Package',
            'created_by' => $admin->id,
            'is_active' => true,
        ]);

        $customer = Customer::factory()->create([
            'name' => 'Client Rajesh',
        ]);

        // 1. Acting as Telecaller A on shoots.create
        $this->actingAs($telecallerA);
        $responseA = $this->get(route('shoots.create'));
        $responseA->assertStatus(200);
        $responseA->assertSee('Alice Luxury Wedding Package');
        $responseA->assertDontSee('Bob Budget Birthday Package');
        $responseA->assertDontSee('Studio Default Premium Package');

        // Telecaller A can successfully book a shoot with their own package
        $shootResponseA = $this->post(route('shoots.store'), [
            'customer_id' => $customer->id,
            'package_id' => $packageA->id,
            'title' => 'Rajesh Wedding',
            'event_type' => 'Wedding',
            'shoot_date' => now()->addDays(10)->toDateString(),
            'venue' => 'Grand Palace',
            'total_amount' => 100000,
        ]);
        $shootResponseA->assertRedirect();
        $this->assertDatabaseHas('shoots', [
            'customer_id' => $customer->id,
            'package_id' => $packageA->id,
            'created_by' => $telecallerA->id,
        ]);

        // Telecaller A CANNOT select a package created by Telecaller B
        $shootResponseInvalid = $this->from(route('shoots.create'))->post(route('shoots.store'), [
            'customer_id' => $customer->id,
            'package_id' => $packageB->id,
            'title' => 'Rajesh Birthday',
            'event_type' => 'Birthday',
            'shoot_date' => now()->addDays(15)->toDateString(),
            'venue' => 'Hotel Royal',
            'total_amount' => 30000,
        ]);
        $shootResponseInvalid->assertRedirect(route('shoots.create'));
        $shootResponseInvalid->assertSessionHasErrors('package_id');

        // 2. Acting as Admin Alex on shoots.create
        $this->actingAs($admin);
        $responseAdmin = $this->get(route('shoots.create'));
        $responseAdmin->assertStatus(200);
        // Admin sees all packages
        $responseAdmin->assertSee('Alice Luxury Wedding Package');
        $responseAdmin->assertSee('Bob Budget Birthday Package');
        $responseAdmin->assertSee('Studio Default Premium Package');

        // Admin can book with any package
        $shootResponseAdmin = $this->post(route('shoots.store'), [
            'customer_id' => $customer->id,
            'package_id' => $packageB->id,
            'title' => 'Admin Booked Shoot',
            'event_type' => 'Birthday',
            'shoot_date' => now()->addDays(20)->toDateString(),
            'venue' => 'Sky Lounge',
            'total_amount' => 40000,
        ]);
        $shootResponseAdmin->assertRedirect();
        $this->assertDatabaseHas('shoots', [
            'package_id' => $packageB->id,
            'created_by' => $admin->id,
        ]);
    }

    public function test_leads_index_displays_telecaller_performance_cards_and_status_counts(): void
    {
        $telecaller = User::factory()->create([
            'name' => 'Kiran Gowda',
            'role' => 'telecaller',
        ]);

        // Create 2 leads today by Kiran (1 new, 1 converted)
        Lead::factory()->create([
            'customer_name' => 'Lead Today One',
            'created_by' => $telecaller->id,
            'status' => 'new',
            'estimated_budget' => 50000,
            'created_at' => now(),
        ]);

        Lead::factory()->create([
            'customer_name' => 'Lead Today Two Converted',
            'created_by' => $telecaller->id,
            'status' => 'converted',
            'estimated_budget' => 80000,
            'created_at' => now(),
        ]);

        // Create 1 lead in discussion
        Lead::factory()->create([
            'customer_name' => 'Lead Discussion',
            'created_by' => $telecaller->id,
            'status' => 'in_discussion',
            'estimated_budget' => 60000,
            'created_at' => now()->subDays(2),
        ]);

        $this->actingAs($telecaller);

        $response = $this->get(route('leads.index'));

        $response->assertStatus(200);

        // Asserts KPI Summary Cards are rendered
        $response->assertSee("Today's New Leads");
        $response->assertSee('2 Leads');
        $response->assertSee('Active In Follow-up');
        $response->assertSee('2 Prospects'); // 'new' and 'in_discussion'
        $response->assertSee('Converted to Bookings');
        $response->assertSee('1 Converted');
        $response->assertSee('Total Leads');
        $response->assertSee('3 Leads');
        $response->assertSee('Pipeline Potential Value');

        // Asserts Status Tabs show number pills
        $response->assertSee('All Leads');
        $response->assertSee('New Enquiry');
        $response->assertSee('In Discussion');
        $response->assertSee('Converted');
    }

    public function test_telecaller_has_full_access_to_all_panel_sections(): void
    {
        $telecaller = User::factory()->create([
            'name' => 'Operational Telecaller',
            'role' => 'telecaller',
        ]);

        $this->actingAs($telecaller);

        // Verify all panel routes return HTTP 200 OK without 403 Forbidden
        $routes = [
            route('dashboard'),
            route('leads.index'),
            route('customers.index'),
            route('packages.index'),
            route('shoots.index'),
            route('payments.index'),
            route('invoices.index'),
            route('reports.index'),
            route('settings.index'),
            route('photographers.index'),
            route('crew.index'),
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertStatus(200);
        }
    }

    public function test_telecaller_only_sees_own_leads_in_leads_index(): void
    {
        $telecallerA = User::factory()->create(['name' => 'Telecaller Alice', 'role' => 'telecaller']);
        $telecallerB = User::factory()->create(['name' => 'Telecaller Bob', 'role' => 'telecaller']);

        $leadA = Lead::factory()->create([
            'customer_name' => 'Alice Client Lead',
            'created_by' => $telecallerA->id,
            'assigned_to' => $telecallerA->id,
        ]);

        $leadB = Lead::factory()->create([
            'customer_name' => 'Bob Secret Lead',
            'created_by' => $telecallerB->id,
            'assigned_to' => $telecallerB->id,
        ]);

        // When Telecaller Alice visits /leads
        $this->actingAs($telecallerA);
        $response = $this->get(route('leads.index'));

        $response->assertStatus(200);
        $response->assertSee('Alice Client Lead');
        $response->assertDontSee('Bob Secret Lead');

        // Cannot view Bob's lead
        $this->get(route('leads.show', $leadB))->assertStatus(403);
    }

    public function test_telecaller_only_sees_own_shoots_in_shoots_index(): void
    {
        $telecallerA = User::factory()->create(['name' => 'Telecaller Alice', 'role' => 'telecaller']);
        $telecallerB = User::factory()->create(['name' => 'Telecaller Bob', 'role' => 'telecaller']);

        $shootA = Shoot::factory()->create([
            'title' => 'Alice Private Shoot',
            'created_by' => $telecallerA->id,
        ]);

        $shootB = Shoot::factory()->create([
            'title' => 'Bob Private Shoot',
            'created_by' => $telecallerB->id,
        ]);

        // When Telecaller Alice visits /shoots
        $this->actingAs($telecallerA);
        $response = $this->get(route('shoots.index'));

        $response->assertStatus(200);
        $response->assertSee('Alice Private Shoot');
        $response->assertDontSee('Bob Private Shoot');

        // Cannot view Bob's shoot
        $this->get(route('shoots.show', $shootB))->assertStatus(403);
    }

    public function test_telecaller_only_sees_own_packages_in_packages_index(): void
    {
        $telecallerA = User::factory()->create(['name' => 'Telecaller Alice', 'role' => 'telecaller']);
        $telecallerB = User::factory()->create(['name' => 'Telecaller Bob', 'role' => 'telecaller']);

        $packageA = Package::factory()->create([
            'name' => 'Alice Gold Package',
            'created_by' => $telecallerA->id,
            'is_active' => true,
        ]);

        $packageB = Package::factory()->create([
            'name' => 'Bob Diamond Package',
            'created_by' => $telecallerB->id,
            'is_active' => true,
        ]);

        // When Telecaller Alice visits /packages
        $this->actingAs($telecallerA);
        $response = $this->get(route('packages.index'));

        $response->assertStatus(200);
        $response->assertSee('Alice Gold Package');
        $response->assertDontSee('Bob Diamond Package');

        // Cannot edit Bob's package
        $this->get(route('packages.edit', $packageB))->assertStatus(403);
    }

    public function test_sidebar_badge_shows_telecaller_own_active_shoot_count(): void
    {
        $telecaller = User::factory()->create(['name' => 'Telecaller Tina', 'role' => 'telecaller']);
        $admin = User::factory()->create(['name' => 'Admin Alex', 'role' => 'admin']);

        // 5 active shoots created by admin
        Shoot::factory()->count(5)->create([
            'status' => 'scheduled',
            'created_by' => $admin->id,
        ]);

        // 2 active shoots created by Tina
        Shoot::factory()->count(2)->create([
            'status' => 'scheduled',
            'created_by' => $telecaller->id,
        ]);

        $this->actingAs($telecaller);
        $response = $this->get(route('shoots.index'));

        $response->assertStatus(200);
        // The sidebar badge for Tina should reflect her 2 shoots, not 7
        $response->assertSee('<span class="px-1.5 py-0.5 text-[10px] font-bold rounded-md bg-indigo-100 text-indigo-700">'."\n".'                            2'."\n".'                        </span>', false);
    }

    public function test_leads_search_by_telecaller_name_returns_assigned_leads(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Alex', 'role' => 'admin']);
        $telecaller = User::factory()->create(['name' => 'Kiran Gowda', 'role' => 'telecaller']);
        $otherTelecaller = User::factory()->create(['name' => 'Neha Sharma', 'role' => 'telecaller']);

        $leadKiran1 = Lead::factory()->create([
            'customer_name' => 'Sunil Reddy',
            'assigned_to' => $telecaller->id,
            'created_by' => $telecaller->id,
        ]);

        $leadKiran2 = Lead::factory()->create([
            'customer_name' => 'Kavitha Hegde',
            'assigned_to' => $telecaller->id,
            'created_by' => $telecaller->id,
        ]);

        $leadNeha = Lead::factory()->create([
            'customer_name' => 'Priya Sen',
            'status' => 'contacted',
            'assigned_to' => $otherTelecaller->id,
            'created_by' => $otherTelecaller->id,
        ]);

        // 1. Admin searches "Kiran Gowda"
        $this->actingAs($admin);
        $response = $this->get(route('leads.index', ['search' => 'Kiran Gowda', 'status' => '', 'page' => 1]));
        $response->assertStatus(200);
        $response->assertSee('Sunil Reddy');
        $response->assertSee('Kavitha Hegde');
        $response->assertDontSee('Priya Sen');

        // 2. Kiran logged in searches "Kiran Gowda"
        $this->actingAs($telecaller);
        $responseKiran = $this->get(route('leads.index', ['search' => 'Kiran Gowda', 'status' => '', 'page' => 1]));
        $responseKiran->assertStatus(200);
        $responseKiran->assertSee('Sunil Reddy');
        $responseKiran->assertSee('Kavitha Hegde');
        $responseKiran->assertDontSee('Priya Sen');
    }

    public function test_leads_filter_by_telecaller_matches_both_created_by_and_assigned_to(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $telecallerA = User::factory()->create(['name' => 'Kiran Gowda', 'role' => 'telecaller']);
        $telecallerB = User::factory()->create(['name' => 'Neha Sharma', 'role' => 'telecaller']);

        // Lead assigned to Kiran but created_by null / admin
        $leadAssignedToKiran = Lead::factory()->create([
            'customer_name' => 'Kiran Client Assigned',
            'assigned_to' => $telecallerA->id,
            'created_by' => null,
            'status' => 'contacted',
        ]);

        // Lead created by Kiran
        $leadCreatedByKiran = Lead::factory()->create([
            'customer_name' => 'Kiran Client Created',
            'assigned_to' => null,
            'created_by' => $telecallerA->id,
            'status' => 'contacted',
        ]);

        // Lead for Neha
        $leadNeha = Lead::factory()->create([
            'customer_name' => 'Neha Only Client',
            'assigned_to' => $telecallerB->id,
            'created_by' => $telecallerB->id,
            'status' => 'contacted',
        ]);

        $this->actingAs($admin);
        $response = $this->get(route('leads.index', [
            'search' => '',
            'date_type' => 'created_at',
            'date_preset' => 'all',
            'date_from' => '',
            'date_to' => '',
            'created_by' => $telecallerA->id,
            'source' => '',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Kiran Client Assigned');
        $response->assertSee('Kiran Client Created');
        $response->assertDontSee('Neha Only Client');
    }

    public function test_lead_create_page_disables_and_autoselects_telecaller_when_logged_in(): void
    {
        $telecaller = User::factory()->create([
            'name' => 'Kiran Gowda',
            'role' => 'telecaller',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        // 1. When logged in as telecaller
        $this->actingAs($telecaller);
        $response = $this->get(route('leads.create'));
        $response->assertStatus(200);
        $response->assertSee('name="assigned_to" value="'.$telecaller->id.'"', false);
        $response->assertSee('Kiran Gowda (You - Telecaller)');
        $response->assertSee('disabled', false);

        // 2. When logged in as admin
        $this->actingAs($admin);
        $adminResponse = $this->get(route('leads.create'));
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('Unassigned');
        $adminResponse->assertSee('Kiran Gowda (telecaller)');
    }

    public function test_telecaller_creates_lead_enforces_assignment_to_self(): void
    {
        $telecaller = User::factory()->create(['role' => 'telecaller']);
        $this->actingAs($telecaller);

        $response = $this->post(route('leads.store'), [
            'customer_name' => 'Auto Assign Test Client',
            'phone' => '+91 97777 88888',
            'email' => 'autoassign@example.com',
            'event_type' => 'Pre-Wedding',
            'source' => 'Website Form',
            'status' => 'new',
        ]);

        $lead = Lead::where('phone', '+91 97777 88888')->first();
        $this->assertNotNull($lead);
        $this->assertEquals($telecaller->id, $lead->assigned_to);
        $this->assertEquals($telecaller->id, $lead->created_by);
        $response->assertRedirect(route('leads.show', $lead));
    }

    public function test_telecaller_sees_shoots_converted_from_their_leads_and_attribution_badge(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $telecallerA = User::factory()->create(['name' => 'Kiran Gowda', 'role' => 'telecaller']);
        $telecallerB = User::factory()->create(['name' => 'Neha Sharma', 'role' => 'telecaller']);

        // Lead created by Kiran
        $lead = Lead::factory()->create([
            'customer_name' => 'Pooja Hegde',
            'phone' => '+91 91111 22222',
            'created_by' => $telecallerA->id,
            'assigned_to' => $telecallerA->id,
            'status' => 'converted',
        ]);

        // Customer converted from lead
        $customer = Customer::create([
            'name' => 'Pooja Hegde',
            'phone' => '+91 91111 22222',
            'lead_id' => $lead->id,
        ]);

        // Shoot created by Admin for that customer
        $shoot = Shoot::create([
            'shoot_number' => 'SHT-2026-TEST',
            'title' => 'Pooja Hegde — Royal Wedding',
            'customer_id' => $customer->id,
            'lead_id' => $lead->id,
            'event_type' => 'Wedding',
            'shoot_date' => now()->addDays(5)->toDateString(),
            'venue' => 'Taj West End',
            'total_amount' => 100000,
            'balance_amount' => 50000,
            'status' => 'scheduled',
            'created_by' => $admin->id,
        ]);

        // Telecaller A visits /shoots -> can see shoot and attribution badge
        $this->actingAs($telecallerA);
        $responseA = $this->get(route('shoots.index'));
        $responseA->assertStatus(200);
        $responseA->assertSee('SHT-2026-TEST');
        $responseA->assertSee('Converted by:');
        $responseA->assertSee('Kiran Gowda');

        // Telecaller A can view and edit shoot
        $showResponseA = $this->get(route('shoots.show', $shoot));
        $showResponseA->assertStatus(200);
        $showResponseA->assertSee('Converted by:');
        $showResponseA->assertSee('Kiran Gowda');

        $editResponseA = $this->get(route('shoots.edit', $shoot));
        $editResponseA->assertStatus(200);

        // Telecaller B visits /shoots -> cannot see Telecaller A's shoot
        $this->actingAs($telecallerB);
        $responseB = $this->get(route('shoots.index'));
        $responseB->assertStatus(200);
        $responseB->assertDontSee('SHT-2026-TEST');

        $showResponseB = $this->get(route('shoots.show', $shoot));
        $showResponseB->assertStatus(403);

        // Admin filters by Telecaller A -> sees shoot
        $this->actingAs($admin);
        $adminFilterA = $this->get(route('shoots.index', ['telecaller_id' => $telecallerA->id]));
        $adminFilterA->assertStatus(200);
        $adminFilterA->assertSee('SHT-2026-TEST');
        $adminFilterA->assertViewHas('shoots', fn ($shoots) => $shoots->contains($shoot));

        // Admin filters by Telecaller B -> does not see shoot
        $adminFilterB = $this->get(route('shoots.index', ['telecaller_id' => $telecallerB->id]));
        $adminFilterB->assertStatus(200);
        $adminFilterB->assertDontSee('SHT-2026-TEST');
        $adminFilterB->assertViewHas('shoots', fn ($shoots) => ! $shoots->contains($shoot));
    }
}
