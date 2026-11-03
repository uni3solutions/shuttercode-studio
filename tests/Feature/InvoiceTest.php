<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Shoot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create([
            'role' => 'admin',
        ]));
    }

    public function test_can_view_invoices_index(): void
    {
        $shoot = Shoot::factory()->create();
        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-888',
            'shoot_id' => $shoot->id,
            'customer_id' => $shoot->customer_id,
            'subtotal' => 100000,
            'tax_rate' => 18,
            'tax_amount' => 18000,
            'discount_amount' => 0,
            'total_amount' => 118000,
            'paid_amount' => 50000,
            'balance_amount' => 68000,
            'status' => 'partially_paid',
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response = $this->get(route('invoices.index'));

        $response->assertStatus(200);
        $response->assertSee('INV-2026-888');
        $response->assertSee(number_format(118000));
    }

    public function test_can_generate_tax_invoice_for_shoot(): void
    {
        $shoot = Shoot::factory()->create([
            'paid_amount' => 25000,
        ]);

        $response = $this->post(route('invoices.store'), [
            'shoot_id' => $shoot->id,
            'subtotal' => 80000,
            'discount_amount' => 5000,
            'tax_rate' => 18,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(15)->format('Y-m-d'),
            'terms' => '50% balance before raw delivery',
        ]);

        $this->assertDatabaseHas('invoices', [
            'shoot_id' => $shoot->id,
            'subtotal' => 80000,
            'discount_amount' => 5000,
            'tax_rate' => 18,
            'tax_amount' => 13500, // (80000 - 5000) * 0.18
            'total_amount' => 88500, // 75000 + 13500
        ]);

        $invoice = Invoice::where('shoot_id', $shoot->id)->first();
        $response->assertRedirect(route('invoices.show', $invoice));
    }
}
