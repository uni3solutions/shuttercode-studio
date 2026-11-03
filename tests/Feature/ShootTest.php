<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Package;
use App\Models\Shoot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShootTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create([
            'role' => 'admin',
        ]));
    }

    public function test_can_view_shoots_index(): void
    {
        $shoot = Shoot::factory()->create([
            'title' => 'Rohan & Ananya Royal Wedding',
        ]);

        $response = $this->get(route('shoots.index'));

        $response->assertStatus(200);
        $response->assertSee('Rohan &amp; Ananya Royal Wedding', false);
    }

    public function test_can_book_new_shoot_with_advance_payment(): void
    {
        $customer = Customer::factory()->create();
        $package = Package::factory()->create([
            'price' => 100000,
        ]);

        $response = $this->post(route('shoots.store'), [
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'title' => 'Karan & Pooja Reception',
            'event_type' => 'Wedding Reception',
            'shoot_date' => now()->addDays(14)->format('Y-m-d'),
            'start_time' => '17:00',
            'end_time' => '23:00',
            'venue' => 'Taj West End, Bengaluru',
            'total_amount' => 100000,
            'discount_amount' => 10000,
            'advance_amount' => 30000,
            'payment_method' => 'upi',
            'transaction_reference' => 'UPI/9988221100',
            'requirements' => 'Traditional + candid photography with 1 drone pilot',
        ]);

        $this->assertDatabaseHas('shoots', [
            'customer_id' => $customer->id,
            'title' => 'Karan & Pooja Reception',
            'total_amount' => 100000,
            'discount_amount' => 10000,
            'status' => 'scheduled',
        ]);

        $shoot = Shoot::where('title', 'Karan & Pooja Reception')->first();
        $response->assertRedirect(route('shoots.show', $shoot));

        // Advance payment was recorded
        $this->assertDatabaseHas('payments', [
            'shoot_id' => $shoot->id,
            'amount' => 30000,
            'payment_method' => 'upi',
            'status' => 'verified',
        ]);

        // Shoot delivery record initialized
        $this->assertDatabaseHas('shoot_deliveries', [
            'shoot_id' => $shoot->id,
            'delivery_status' => 'pending',
        ]);
    }

    public function test_can_assign_crew_member_to_shoot(): void
    {
        $shoot = Shoot::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        $response = $this->post(route('shoots.crew.assign', $shoot), [
            'user_id' => $photographer->id,
            'role' => 'Lead Cinematographer',
            'payout_agreed' => 12000,
            'notes' => 'Sony FX3 with gimbal rig',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('shoot_assignments', [
            'shoot_id' => $shoot->id,
            'user_id' => $photographer->id,
            'role' => 'Lead Cinematographer',
            'payout_agreed' => 12000,
        ]);
    }

    public function test_can_update_shoot_pipeline_status(): void
    {
        $shoot = Shoot::factory()->create(['status' => 'scheduled']);

        $response = $this->post(route('shoots.status', $shoot), [
            'status' => 'in_progress',
        ]);

        $response->assertRedirect();
        $this->assertEquals('in_progress', $shoot->fresh()->status);
    }

    public function test_can_filter_shoots_by_search_query_and_empty_query(): void
    {
        $shoot1 = Shoot::factory()->create(['title' => 'Golden Wedding Sunset', 'event_type' => 'Wedding']);
        $shoot2 = Shoot::factory()->create(['title' => 'Tech Corporate Summit', 'event_type' => 'Corporate']);

        // Empty search returns all
        $responseEmpty = $this->get(route('shoots.index', ['search' => '']));
        $responseEmpty->assertStatus(200);
        $responseEmpty->assertSee('Golden Wedding Sunset');
        $responseEmpty->assertSee('Tech Corporate Summit');

        // Search for specific keyword
        $responseSpecific = $this->get(route('shoots.index', ['search' => 'Sunset']));
        $responseSpecific->assertStatus(200);
        $responseSpecific->assertSee('Golden Wedding Sunset');
        $responseSpecific->assertDontSee('Tech Corporate Summit');
    }

    public function test_can_filter_shoots_by_event_type_and_payment_status(): void
    {
        $dueShoot = Shoot::factory()->create([
            'title' => 'Maternity Garden Shoot',
            'event_type' => 'Maternity',
            'total_amount' => 50000,
            'paid_amount' => 10000,
            'balance_amount' => 40000,
        ]);

        $paidShoot = Shoot::factory()->create([
            'title' => 'Birthday Grand Gala',
            'event_type' => 'Birthday',
            'total_amount' => 30000,
            'paid_amount' => 30000,
            'balance_amount' => 0,
        ]);

        // Filter by event type
        $responseEvent = $this->get(route('shoots.index', ['event_type' => 'Maternity']));
        $responseEvent->assertStatus(200);
        $responseEvent->assertSee('Maternity Garden Shoot');
        $responseEvent->assertDontSee('Birthday Grand Gala');

        // Filter by payment status due
        $responseDue = $this->get(route('shoots.index', ['payment_status' => 'due']));
        $responseDue->assertStatus(200);
        $responseDue->assertSee('Maternity Garden Shoot');
        $responseDue->assertDontSee('Birthday Grand Gala');

        // Filter by payment status paid
        $responsePaid = $this->get(route('shoots.index', ['payment_status' => 'paid']));
        $responsePaid->assertStatus(200);
        $responsePaid->assertSee('Birthday Grand Gala');
        $responsePaid->assertDontSee('Maternity Garden Shoot');
    }

    public function test_shoots_listing_defaults_to_descending_order(): void
    {
        $pastShoot = Shoot::factory()->create([
            'title' => 'Past Shoot Yesterday',
            'shoot_date' => now()->subDay()->toDateString(),
        ]);

        $futureShoot = Shoot::factory()->create([
            'title' => 'Future Shoot Tomorrow',
            'shoot_date' => now()->addDay()->toDateString(),
        ]);

        $response = $this->get(route('shoots.index'));
        $response->assertStatus(200);

        $content = $response->getContent();
        $futurePos = strpos($content, 'Future Shoot Tomorrow');
        $pastPos = strpos($content, 'Past Shoot Yesterday');

        $this->assertNotFalse($futurePos);
        $this->assertNotFalse($pastPos);
        // Future (latest date) appears before past shoot (descending order)
        $this->assertLessThan($pastPos, $futurePos);
    }

    public function test_can_book_shoot_with_crew_assigned_according_to_requirements(): void
    {
        $customer = Customer::factory()->create();
        $package = Package::factory()->create([
            'price' => 150000,
            'photographers_count' => 2,
            'videographers_count' => 1,
            'drone_included' => true,
        ]);

        $photographer1 = User::factory()->create(['role' => 'photographer']);
        $photographer2 = User::factory()->create(['role' => 'photographer']);

        $response = $this->post(route('shoots.store'), [
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'title' => 'Heritage Palace Wedding',
            'event_type' => 'Wedding',
            'shoot_date' => now()->addDays(7)->format('Y-m-d'),
            'venue' => 'Bangalore Palace',
            'total_amount' => 150000,
            'advance_amount' => 50000,
            'payment_method' => 'upi',
            'crew' => [
                [
                    'role' => 'lead_photographer',
                    'user_id' => $photographer1->id,
                    'payout_agreed' => 15000,
                ],
                [
                    'role' => 'candid_photographer',
                    'user_id' => $photographer2->id,
                    'payout_agreed' => 10000,
                ],
            ],
        ]);

        $shoot = Shoot::where('title', 'Heritage Palace Wedding')->first();
        $response->assertRedirect(route('shoots.show', $shoot));

        $this->assertEquals('crew_assigned', $shoot->status);
        $this->assertCount(2, $shoot->assignments);
        $this->assertEquals($photographer1->id, $shoot->assignments[0]->user_id);
    }

    public function test_shoot_details_filters_crew_roles_according_to_package_requirements(): void
    {
        $packagePhotoOnly = Package::factory()->create([
            'photographers_count' => 1,
            'videographers_count' => 0,
            'drone_included' => false,
        ]);

        $shoot = Shoot::factory()->create([
            'package_id' => $packagePhotoOnly->id,
        ]);

        $response = $this->get(route('shoots.show', $shoot));
        $response->assertStatus(200);
        // Only photo roles allowed, no videographer or drone
        $response->assertSee('Lead Candid Photographer');
        $response->assertDontSee('Cinematographer (Video)');
        $response->assertDontSee('Drone Aerial Pilot');
    }
}
