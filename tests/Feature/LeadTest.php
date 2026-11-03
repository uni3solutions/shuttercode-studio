<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create([
            'role' => 'admin',
        ]));
    }

    public function test_can_view_leads_index(): void
    {
        $lead = Lead::factory()->create([
            'customer_name' => 'Meera Nair',
            'phone' => '+91 97766 55443',
            'event_type' => 'Destination Wedding',
        ]);

        $response = $this->get(route('leads.index'));

        $response->assertStatus(200);
        $response->assertSee('Meera Nair');
        $response->assertSee('Destination Wedding');
    }

    public function test_can_create_a_lead_and_logs_initial_note(): void
    {
        $response = $this->post(route('leads.store'), [
            'customer_name' => 'Kavita Sharma',
            'phone' => '+91 91234 56789',
            'email' => 'kavita@example.com',
            'event_type' => 'Pre-Wedding Shoot',
            'source' => 'WhatsApp Meta',
            'status' => 'new',
            'venue' => 'Udaipur Palace',
            'estimated_budget' => 120000,
            'notes' => 'Client wants a 2-day shoot with drone videography',
        ]);

        $this->assertDatabaseHas('leads', [
            'customer_name' => 'Kavita Sharma',
            'phone' => '+91 91234 56789',
            'event_type' => 'Pre-Wedding Shoot',
        ]);

        $lead = Lead::where('phone', '+91 91234 56789')->first();
        $response->assertRedirect(route('leads.show', $lead));

        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'type' => 'note',
            'details' => 'Client wants a 2-day shoot with drone videography',
        ]);
    }

    public function test_can_add_follow_up_activity_to_lead(): void
    {
        $lead = Lead::factory()->create();

        $response = $this->post(route('leads.activities.store', $lead), [
            'type' => 'call',
            'details' => 'Discussed luxury wedding package quotation over call',
            'status' => 'in_discussion',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'type' => 'call',
            'details' => 'Discussed luxury wedding package quotation over call',
        ]);

        $this->assertEquals('in_discussion', $lead->fresh()->status);
    }

    public function test_can_convert_lead_to_customer(): void
    {
        $lead = Lead::factory()->create([
            'customer_name' => 'Divya & Vikram',
            'phone' => '+91 98888 77777',
            'email' => 'divya@example.com',
            'event_type' => 'Wedding',
            'venue' => 'Jaipur, Rajasthan',
        ]);

        $response = $this->post(route('leads.convert', $lead));

        $this->assertDatabaseHas('customers', [
            'name' => 'Divya & Vikram',
            'phone' => '+91 98888 77777',
            'email' => 'divya@example.com',
        ]);

        $this->assertEquals('converted', $lead->fresh()->status);
    }

    public function test_lead_pipeline_statuses_and_variants(): void
    {
        $statuses = Lead::statuses();
        $this->assertArrayHasKey('new', $statuses);
        $this->assertArrayHasKey('contacted', $statuses);
        $this->assertArrayHasKey('in_discussion', $statuses);
        $this->assertArrayHasKey('interested', $statuses);
        $this->assertArrayHasKey('follow_up_later', $statuses);
        $this->assertArrayHasKey('not_responding', $statuses);
        $this->assertArrayHasKey('not_interested', $statuses);
        $this->assertArrayHasKey('converted', $statuses);

        $leadInterested = Lead::factory()->create(['status' => 'interested']);
        $this->assertEquals('Interested', $leadInterested->status_label);
        $this->assertEquals('purple', $leadInterested->status_variant);

        $leadFollowUp = Lead::factory()->create(['status' => 'follow_up_later']);
        $this->assertEquals('Follow Up Later', $leadFollowUp->status_label);
        $this->assertEquals('indigo', $leadFollowUp->status_variant);

        $leadNotResponding = Lead::factory()->create(['status' => 'not_responding']);
        $this->assertEquals('Not Responding', $leadNotResponding->status_label);
        $this->assertEquals('neutral', $leadNotResponding->status_variant);

        $leadNotInterested = Lead::factory()->create(['status' => 'not_interested']);
        $this->assertEquals('Not Interested', $leadNotInterested->status_label);
        $this->assertEquals('danger', $leadNotInterested->status_variant);
    }

    public function test_can_filter_leads_by_status(): void
    {
        Lead::factory()->create([
            'customer_name' => 'Alice Interested',
            'status' => 'interested',
        ]);
        Lead::factory()->create([
            'customer_name' => 'Bob Not Interested',
            'status' => 'not_interested',
        ]);

        $response = $this->get(route('leads.index', ['status' => 'interested']));
        $response->assertStatus(200);
        $response->assertSee('Alice Interested');
        $response->assertDontSee('Bob Not Interested');
    }

    public function test_can_update_lead_to_new_statuses_via_edit(): void
    {
        $lead = Lead::factory()->create(['status' => 'new']);

        $response = $this->put(route('leads.update', $lead), [
            'customer_name' => $lead->customer_name,
            'phone' => $lead->phone,
            'email' => $lead->email,
            'event_type' => $lead->event_type,
            'source' => $lead->source,
            'status' => 'follow_up_later',
            'estimated_budget' => $lead->estimated_budget,
        ]);

        $response->assertRedirect(route('leads.show', $lead));
        $this->assertEquals('follow_up_later', $lead->fresh()->status);
    }

    public function test_can_filter_leads_by_custom_date_range(): void
    {
        $oldLead = Lead::factory()->create([
            'customer_name' => 'Old Lead Person',
            'created_at' => now()->subDays(30),
            'status' => 'contacted',
        ]);

        $recentLead = Lead::factory()->create([
            'customer_name' => 'Recent Lead Person',
            'created_at' => now()->subDays(2),
            'status' => 'contacted',
        ]);

        $response = $this->get(route('leads.index', [
            'date_from' => now()->subDays(5)->toDateString(),
            'date_to' => now()->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertSee('Recent Lead Person');
        $response->assertDontSee('Old Lead Person');
    }

    public function test_can_filter_leads_by_date_preset_today(): void
    {
        $todayLead = Lead::factory()->create([
            'customer_name' => 'Today Customer',
            'created_at' => now(),
            'status' => 'contacted',
        ]);

        $yesterdayLead = Lead::factory()->create([
            'customer_name' => 'Yesterday Customer',
            'created_at' => now()->subDays(2),
            'status' => 'contacted',
        ]);

        $response = $this->get(route('leads.index', [
            'date_preset' => 'today',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Today Customer');
        $response->assertDontSee('Yesterday Customer');
    }

    public function test_can_filter_leads_by_event_date(): void
    {
        $decemberEvent = Lead::factory()->create([
            'customer_name' => 'December Wedding Client',
            'event_date' => '2026-12-15',
            'status' => 'contacted',
        ]);

        $januaryEvent = Lead::factory()->create([
            'customer_name' => 'January Birthday Client',
            'event_date' => '2027-01-20',
            'status' => 'contacted',
        ]);

        $response = $this->get(route('leads.index', [
            'date_type' => 'event_date',
            'date_from' => '2026-12-01',
            'date_to' => '2026-12-31',
        ]));

        $response->assertStatus(200);
        $response->assertSee('December Wedding Client');
        $response->assertDontSee('January Birthday Client');
    }
}
