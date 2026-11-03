<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Shoot;
use App\Models\StudioSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create([
            'role' => 'admin',
        ]));
    }

    public function test_can_view_settings_page(): void
    {
        $response = $this->get(route('settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Studio & Invoice Settings');
        $response->assertSee('GST & Taxation Configuration');
        $response->assertSee('HDFC Bank Ltd');
    }

    public function test_can_update_studio_and_invoice_settings(): void
    {
        $response = $this->put(route('settings.update'), [
            'studio_name' => 'Royal ShutterCode Studios',
            'studio_tagline' => 'Luxury Destination Wedding Cinematography',
            'phone' => '+91 99999 88888',
            'email' => 'accounts@royalshuttercode.com',
            'address' => '108, UB City Tower',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'pincode' => '560001',
            'gstin' => '29BBBBB1111B2Z6',
            'default_tax_rate' => 18.00,
            'hsn_sac_code' => '9983',
            'bank_name' => 'ICICI Bank',
            'bank_account_name' => 'Royal ShutterCode LLP',
            'bank_account_number' => '123405009988',
            'bank_ifsc' => 'ICIC0001234',
            'bank_branch' => 'MG Road, Bengaluru',
            'upi_id' => 'royalshutter@icici',
            'invoice_prefix' => 'ROYAL',
            'invoice_terms' => 'Strict 14 day payment cycle.',
            'invoice_notes' => 'Thank you for choosing Royal ShutterCode.',
            'authorized_signatory_name' => 'Vikram Malhotra',
            'authorized_signatory_title' => 'Managing Director',
        ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('studio_settings', [
            'studio_name' => 'Royal ShutterCode Studios',
            'gstin' => '29BBBBB1111B2Z6',
            'upi_id' => 'royalshutter@icici',
        ]);
    }

    public function test_invoice_displays_custom_studio_branding_and_settings(): void
    {
        StudioSetting::current()->update([
            'studio_name' => 'Aura Vision Studios',
            'gstin' => '29AABCA1234Z1Z9',
            'upi_id' => 'auravision@upi',
        ]);

        $shoot = Shoot::factory()->create([
            'total_amount' => 100000,
            'discount_amount' => 0,
            'paid_amount' => 100000,
            'balance_amount' => 0,
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-888',
            'shoot_id' => $shoot->id,
            'customer_id' => $shoot->customer_id,
            'subtotal' => 100000,
            'tax_rate' => 18.00,
            'tax_amount' => 18000,
            'discount_amount' => 0,
            'total_amount' => 118000,
            'paid_amount' => 118000,
            'balance_amount' => 0,
            'status' => 'paid',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        $response = $this->get(route('invoices.show', $invoice));

        $response->assertStatus(200);
        $response->assertSee('Aura Vision Studios');
        $response->assertSee('29AABCA1234Z1Z9');
        $response->assertSee('auravision@upi');
        $response->assertSee('Fully Paid');
        $response->assertSee('Rupees One Lakh Eighteen Thousand Only');
    }
}
