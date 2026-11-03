<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Shoot;
use App\Models\StudioSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelecallerInvoiceSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_telecaller_can_view_and_update_own_invoice_settings(): void
    {
        $telecaller = User::factory()->create([
            'name' => 'Neha Sharma',
            'role' => 'telecaller',
        ]);

        $this->actingAs($telecaller);

        // Visit settings page as telecaller
        $response = $this->get(route('settings.index'));
        $response->assertOk();
        $response->assertSee('Neha Sharma');
        $response->assertSee('Telecaller Profile');

        // Update settings
        $responseUpdate = $this->put(route('settings.update'), [
            'studio_name' => 'Neha Sharma Weddings & Films',
            'studio_tagline' => 'Luxury Moments by Neha',
            'phone' => '+91 98450 11111',
            'email' => 'neha.billing@shuttercode.com',
            'address' => 'Indiranagar 12th Main',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'pincode' => '560038',
            'gstin' => '29NEHA1234Z1Z1',
            'default_tax_rate' => 18.00,
            'hsn_sac_code' => '9983',
            'bank_name' => 'Axis Bank',
            'bank_account_name' => 'Neha Sharma Photography',
            'bank_account_number' => '919010012345678',
            'bank_ifsc' => 'UTIB0000123',
            'bank_branch' => 'Indiranagar',
            'upi_id' => 'neha@axisbank',
            'invoice_prefix' => 'NEHA',
            'invoice_terms' => 'Strict 10 day payment window.',
            'invoice_notes' => 'Thank you for booking with Neha Sharma!',
            'authorized_signatory_name' => 'Neha Sharma',
            'authorized_signatory_title' => 'Senior Client Partner',
        ]);

        $responseUpdate->assertRedirect();
        $this->assertDatabaseHas('studio_settings', [
            'user_id' => $telecaller->id,
            'invoice_prefix' => 'NEHA',
            'upi_id' => 'neha@axisbank',
            'authorized_signatory_name' => 'Neha Sharma',
        ]);
    }

    public function test_invoice_generates_based_on_lead_assigned_telecaller_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $telecallerNeha = User::factory()->create(['name' => 'Neha Sharma', 'role' => 'telecaller']);
        $telecallerKiran = User::factory()->create(['name' => 'Kiran Gowda', 'role' => 'telecaller']);

        // Configure custom invoice settings for Neha
        StudioSetting::create([
            'user_id' => $telecallerNeha->id,
            'studio_name' => 'Neha ShutterCode Branch',
            'phone' => '+91 98450 11111',
            'email' => 'neha@shuttercode.com',
            'address' => 'Bengaluru East',
            'city' => 'Bengaluru',
            'gstin' => '29NEHA9999Z1Z1',
            'default_tax_rate' => 18.00,
            'bank_name' => 'HDFC Bank',
            'bank_account_name' => 'Neha Sharma Collections',
            'bank_account_number' => '50100099887766',
            'bank_ifsc' => 'HDFC0009999',
            'upi_id' => 'nehasharma@hdfc',
            'invoice_prefix' => 'NEHA',
            'authorized_signatory_name' => 'Neha Sharma',
            'authorized_signatory_title' => 'Lead Client Consultant',
        ]);

        // Configure custom invoice settings for Kiran
        StudioSetting::create([
            'user_id' => $telecallerKiran->id,
            'studio_name' => 'Kiran Gowda Media',
            'phone' => '+91 98450 22222',
            'email' => 'kiran@shuttercode.com',
            'address' => 'Bengaluru South',
            'city' => 'Bengaluru',
            'gstin' => '29KIRA8888Z1Z2',
            'default_tax_rate' => 18.00,
            'bank_name' => 'ICICI Bank',
            'bank_account_name' => 'Kiran Gowda Media Account',
            'bank_account_number' => '123405001122',
            'bank_ifsc' => 'ICIC0005555',
            'upi_id' => 'kirangowda@icici',
            'invoice_prefix' => 'KIRAN',
            'authorized_signatory_name' => 'Kiran Gowda',
            'authorized_signatory_title' => 'Lead Relationship Officer',
        ]);

        // Create Lead assigned to Neha
        $leadNeha = Lead::create([
            'lead_number' => 'LED-2026-101',
            'customer_name' => 'Anil Kapoor',
            'phone' => '+91 98450 33333',
            'event_type' => 'Destination Wedding',
            'assigned_to' => $telecallerNeha->id,
            'created_by' => $telecallerNeha->id,
            'status' => 'converted',
        ]);

        $customerNeha = Customer::create([
            'name' => 'Anil Kapoor',
            'phone' => '+91 98450 33333',
            'lead_id' => $leadNeha->id,
        ]);

        $shootNeha = Shoot::create([
            'shoot_number' => 'SHT-2026-101',
            'title' => 'Anil & Sunita Wedding',
            'customer_id' => $customerNeha->id,
            'lead_id' => $leadNeha->id,
            'event_type' => 'Destination Wedding',
            'shoot_date' => now()->toDateString(),
            'venue' => 'Palace Grounds, Bengaluru',
            'total_amount' => 100000,
            'discount_amount' => 0,
            'advance_amount' => 20000,
            'paid_amount' => 20000,
            'balance_amount' => 80000,
            'status' => 'scheduled',
            'created_by' => $telecallerNeha->id,
        ]);

        // Generate invoice for Neha's shoot
        $this->actingAs($admin);
        $response = $this->post(route('invoices.store'), [
            'shoot_id' => $shootNeha->id,
            'subtotal' => 100000,
            'tax_rate' => 18.00,
            'discount_amount' => 0,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
        ]);

        $response->assertRedirect();
        $invoiceNeha = Invoice::where('shoot_id', $shootNeha->id)->first();
        $this->assertNotNull($invoiceNeha);

        // Verify invoice was generated with Neha's prefix
        $this->assertStringStartsWith('NEHA-', $invoiceNeha->invoice_number);
        $this->assertEquals($telecallerNeha->id, $invoiceNeha->telecaller_id);
        $this->assertEquals($leadNeha->id, $invoiceNeha->lead_id);

        // View invoice and assert Neha's details render
        $showResponse = $this->get(route('invoices.show', $invoiceNeha));
        $showResponse->assertOk();
        $showResponse->assertSee('Neha ShutterCode Branch');
        $showResponse->assertSee('nehasharma@hdfc');
        $showResponse->assertSee('29NEHA9999Z1Z1');
        $showResponse->assertSee('Neha Sharma');

        // Now test Kiran's lead
        $leadKiran = Lead::create([
            'lead_number' => 'LED-2026-202',
            'customer_name' => 'Meera Patel',
            'phone' => '+91 98450 44444',
            'event_type' => 'Baby Shoot',
            'assigned_to' => $telecallerKiran->id,
            'created_by' => $telecallerKiran->id,
            'status' => 'converted',
        ]);

        $customerKiran = Customer::create([
            'name' => 'Meera Patel',
            'phone' => '+91 98450 44444',
            'lead_id' => $leadKiran->id,
        ]);

        $shootKiran = Shoot::create([
            'shoot_number' => 'SHT-2026-202',
            'title' => 'Aarav 1st Birthday & Baby Shoot',
            'customer_id' => $customerKiran->id,
            'lead_id' => $leadKiran->id,
            'event_type' => 'Baby Shoot',
            'shoot_date' => now()->toDateString(),
            'venue' => 'Whitefield Studio, Bengaluru',
            'total_amount' => 40000,
            'discount_amount' => 0,
            'advance_amount' => 10000,
            'paid_amount' => 10000,
            'balance_amount' => 30000,
            'status' => 'scheduled',
            'created_by' => $telecallerKiran->id,
        ]);

        $responseKiran = $this->post(route('invoices.store'), [
            'shoot_id' => $shootKiran->id,
            'subtotal' => 40000,
            'tax_rate' => 18.00,
            'discount_amount' => 0,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
        ]);

        $responseKiran->assertRedirect();
        $invoiceKiran = Invoice::where('shoot_id', $shootKiran->id)->first();
        $this->assertNotNull($invoiceKiran);

        // Verify invoice was generated with Kiran's prefix
        $this->assertStringStartsWith('KIRAN-', $invoiceKiran->invoice_number);
        $this->assertEquals($telecallerKiran->id, $invoiceKiran->telecaller_id);
        $this->assertEquals($leadKiran->id, $invoiceKiran->lead_id);

        // View invoice and assert Kiran's details render
        $showResponseKiran = $this->get(route('invoices.show', $invoiceKiran));
        $showResponseKiran->assertOk();
        $showResponseKiran->assertSee('Kiran Gowda Media');
        $showResponseKiran->assertSee('kirangowda@icici');
        $showResponseKiran->assertSee('29KIRA8888Z1Z2');
        $showResponseKiran->assertSee('Kiran Gowda');
    }
}
