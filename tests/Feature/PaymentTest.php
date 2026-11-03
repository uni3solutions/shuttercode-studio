<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Shoot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create([
            'role' => 'admin',
        ]));
    }

    public function test_can_view_payments_ledger(): void
    {
        $shoot = Shoot::factory()->create();
        Payment::create([
            'payment_number' => 'PAY-2026-999',
            'shoot_id' => $shoot->id,
            'customer_id' => $shoot->customer_id,
            'amount' => 45000,
            'payment_method' => 'upi',
            'payment_type' => 'advance',
            'status' => 'verified',
            'paid_at' => now(),
        ]);

        $response = $this->get(route('payments.index'));

        $response->assertStatus(200);
        $response->assertSee('PAY-2026-999');
        $response->assertSee('45,000');
    }

    public function test_recording_payment_automatically_recalculates_shoot_balance(): void
    {
        $shoot = Shoot::factory()->create([
            'total_amount' => 100000,
            'discount_amount' => 0,
            'paid_amount' => 0,
            'balance_amount' => 100000,
        ]);

        $response = $this->post(route('payments.store'), [
            'shoot_id' => $shoot->id,
            'amount' => 40000,
            'payment_method' => 'bank_transfer',
            'payment_type' => 'milestone',
            'transaction_reference' => 'NEFT2026101899',
            'paid_at' => now()->format('Y-m-d'),
            'notes' => 'Milestone payment before shoot day',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'shoot_id' => $shoot->id,
            'amount' => 40000,
            'status' => 'verified',
        ]);

        // Shoot's paid and balance amounts automatically updated by Payment booted observer
        $freshShoot = $shoot->fresh();
        $this->assertEquals(40000, $freshShoot->paid_amount);
        $this->assertEquals(60000, $freshShoot->balance_amount);

        // Payment is linked to an invoice and shoot view shows remaining balance
        $payment = Payment::where('shoot_id', $shoot->id)->first();
        $this->assertNotNull($payment->invoice_id);
        $this->assertDatabaseHas('invoices', [
            'id' => $payment->invoice_id,
            'shoot_id' => $shoot->id,
        ]);

        $showResponse = $this->get(route('shoots.show', $shoot));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Partial');
        $showResponse->assertSee('Remaining: ₹60,000');
        $showResponse->assertSee($payment->invoice->invoice_number);
    }

    public function test_fully_paid_shoot_displays_fully_paid_status(): void
    {
        $shoot = Shoot::factory()->create([
            'total_amount' => 50000,
            'discount_amount' => 0,
            'paid_amount' => 0,
            'balance_amount' => 50000,
        ]);

        $response = $this->post(route('payments.store'), [
            'shoot_id' => $shoot->id,
            'amount' => 50000,
            'payment_method' => 'upi',
            'payment_type' => 'final_payment',
            'transaction_reference' => 'UPI-FULL-SETTLE',
            'paid_at' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $freshShoot = $shoot->fresh();
        $this->assertEquals(0, $freshShoot->balance_amount);

        $showResponse = $this->get(route('shoots.show', $shoot));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Fully Paid');
        $showResponse->assertSee('Zero Balance');
    }
}
