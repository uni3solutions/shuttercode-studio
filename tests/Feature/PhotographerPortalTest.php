<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Package;
use App\Models\PhotographerPayment;
use App\Models\Shoot;
use App\Models\ShootAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhotographerPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_photographer_cannot_access_admin_management_routes(): void
    {
        $photographer = User::factory()->create([
            'role' => 'photographer',
        ]);

        $this->actingAs($photographer);

        // Accessing main dashboard redirects to photographer dashboard
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('photographers.dashboard'));

        // Accessing admin routes redirects back to portal with restricted notice
        $adminRoutes = [
            route('shoots.index'),
            route('customers.index'),
            route('payments.index'),
            route('invoices.index'),
            route('reports.index'),
            route('settings.index'),
        ];

        foreach ($adminRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect(route('photographers.portal'));
            $response->assertSessionHas('error');
        }
    }

    public function test_photographer_can_view_dashboard_with_lifetime_income_and_shoot_counts(): void
    {
        $photographer = User::factory()->create([
            'name' => 'Arun Kumar',
            'role' => 'photographer',
        ]);

        $shoot = Shoot::factory()->create(['status' => 'in_progress']);

        ShootAssignment::create([
            'shoot_id' => $shoot->id,
            'user_id' => $photographer->id,
            'payout_agreed' => 15000.00,
            'role' => 'lead_photographer',
        ]);

        PhotographerPayment::create([
            'payment_number' => 'CPAY-2026-TEST',
            'shoot_id' => $shoot->id,
            'user_id' => $photographer->id,
            'amount' => 8000.00,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->actingAs($photographer);

        $response = $this->get(route('photographers.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Total Working Income');
        $response->assertSee('8,000'); // Lifetime income
        $response->assertSee('7,000'); // Pending balance (15000 - 8000)
        $response->assertSee('Pending Shoots');
    }

    public function test_photographer_sees_only_assigned_shoots_and_fixed_payout(): void
    {
        $photographer = User::factory()->create([
            'name' => 'Arun Kumar',
            'role' => 'photographer',
        ]);

        $otherPhotographer = User::factory()->create([
            'name' => 'Other Artist',
            'role' => 'photographer',
        ]);

        $customer = Customer::factory()->create([
            'name' => 'Aarav & Divya',
        ]);

        // Shoot assigned to Arun
        $myShoot = Shoot::factory()->create([
            'customer_id' => $customer->id,
            'title' => 'Aarav Pre-Wedding',
            'venue' => 'Nandi Hills Resort',
            'total_amount' => 95000.00, // Should NOT be disclosed to photographer
            'status' => 'crew_assigned',
        ]);

        $assignment = ShootAssignment::create([
            'shoot_id' => $myShoot->id,
            'user_id' => $photographer->id,
            'role' => 'lead_photographer',
            'payout_agreed' => 12000.00,
            'status' => 'confirmed',
            'notes' => 'Bring wide angle lens and drone.',
        ]);

        // Record partial payment to photographer
        PhotographerPayment::create([
            'payment_number' => 'CPAY-2026-001',
            'shoot_id' => $myShoot->id,
            'user_id' => $photographer->id,
            'amount' => 5000.00,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Another shoot NOT assigned to Arun
        $otherShoot = Shoot::factory()->create([
            'title' => 'Secret VIP Shoot',
            'total_amount' => 250000.00,
        ]);

        ShootAssignment::create([
            'shoot_id' => $otherShoot->id,
            'user_id' => $otherPhotographer->id,
            'role' => 'cinematographer',
            'payout_agreed' => 20000.00,
        ]);

        $this->actingAs($photographer);

        $response = $this->get(route('photographers.portal'));

        $response->assertStatus(200);
        // Can see their assigned shoot details
        $response->assertSee('Aarav Pre-Wedding');
        $response->assertSee('Customer: Aarav & Divya');
        $response->assertSee('Nandi Hills Resort');
        $response->assertSee('Bring wide angle lens and drone.');

        // Can see fixed agreed payout, paid amount, and remaining balance
        $response->assertSee('12,000'); // Payout agreed
        $response->assertSee('5,000');  // Paid by agency
        $response->assertSee('7,000');  // Balance left (12000 - 5000)

        // Cannot see the unassigned shoot
        $response->assertDontSee('Secret VIP Shoot');

        // Cannot see client total package price
        $response->assertDontSee('95,000');
        $response->assertDontSee('250,000');
    }

    public function test_photographer_can_filter_shoots_by_categories(): void
    {
        $photographer = User::factory()->create(['role' => 'photographer']);

        $todayShoot = Shoot::factory()->create([
            'title' => 'Today Special Shoot',
            'shoot_date' => now()->toDateString(),
        ]);

        $futureShoot = Shoot::factory()->create([
            'title' => 'Future Next Month Shoot',
            'shoot_date' => now()->addDays(15)->toDateString(),
        ]);

        ShootAssignment::create([
            'shoot_id' => $todayShoot->id,
            'user_id' => $photographer->id,
            'payout_agreed' => 8000,
        ]);

        ShootAssignment::create([
            'shoot_id' => $futureShoot->id,
            'user_id' => $photographer->id,
            'payout_agreed' => 9000,
        ]);

        $this->actingAs($photographer);

        // Filter by today
        $responseToday = $this->get(route('photographers.portal', ['category' => 'today']));
        $responseToday->assertStatus(200);
        $responseToday->assertSee('Today Special Shoot');
        $responseToday->assertDontSee('Future Next Month Shoot');

        // Filter by upcoming
        $responseUpcoming = $this->get(route('photographers.portal', ['category' => 'upcoming']));
        $responseUpcoming->assertStatus(200);
        $responseUpcoming->assertSee('Future Next Month Shoot');
        $responseUpcoming->assertDontSee('Today Special Shoot');
    }

    public function test_photographer_can_view_payouts_and_invoices_ledger(): void
    {
        $photographer = User::factory()->create(['role' => 'photographer']);
        $shoot = Shoot::factory()->create(['title' => 'Heritage Wedding']);

        ShootAssignment::create([
            'shoot_id' => $shoot->id,
            'user_id' => $photographer->id,
            'payout_agreed' => 18000,
        ]);

        PhotographerPayment::create([
            'payment_number' => 'CPAY-2026-999',
            'shoot_id' => $shoot->id,
            'user_id' => $photographer->id,
            'amount' => 12000,
            'payment_method' => 'upi',
            'transaction_reference' => 'UPI/9988776655',
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->actingAs($photographer);

        $response = $this->get(route('photographers.payments'));
        $response->assertStatus(200);
        $response->assertSee('CPAY-2026-999');
        $response->assertSee('12,000');
        $response->assertSee('Heritage Wedding');
        $response->assertSee('View Invoice');
        $response->assertSee(route('photographers.shoots.invoice', $shoot));
    }

    public function test_photographer_can_submit_raw_drive_link(): void
    {
        $photographer = User::factory()->create([
            'role' => 'photographer',
        ]);

        $shoot = Shoot::factory()->create([
            'status' => 'in_progress',
        ]);

        ShootAssignment::create([
            'shoot_id' => $shoot->id,
            'user_id' => $photographer->id,
            'role' => 'lead_photographer',
            'payout_agreed' => 10000.00,
        ]);

        $this->actingAs($photographer);

        $response = $this->post(route('photographers.delivery', $shoot), [
            'raw_drive_link' => 'https://drive.google.com/drive/folders/test-raw-upload',
            'raw_photos_count' => 850,
            'notes' => 'Cards ingested without issue',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shoot_deliveries', [
            'shoot_id' => $shoot->id,
            'raw_drive_link' => 'https://drive.google.com/drive/folders/test-raw-upload',
            'raw_photos_count' => 850,
        ]);

        $this->assertDatabaseHas('shoots', [
            'id' => $shoot->id,
            'status' => 'raw_uploaded',
        ]);
    }

    public function test_photographer_cannot_submit_delivery_for_unassigned_shoot(): void
    {
        $photographer = User::factory()->create([
            'role' => 'photographer',
        ]);

        $unassignedShoot = Shoot::factory()->create();

        $this->actingAs($photographer);

        $response = $this->post(route('photographers.delivery', $unassignedShoot), [
            'raw_drive_link' => 'https://drive.google.com/drive/folders/unauthorized',
        ]);

        $response->assertStatus(403);
    }

    public function test_photographer_can_view_remuneration_invoice(): void
    {
        $photographer = User::factory()->create(['role' => 'photographer', 'name' => 'Arun Kumar']);
        $shoot = Shoot::factory()->create(['title' => 'Grand Royal Wedding', 'shoot_number' => 'SHT-2026-999']);

        ShootAssignment::create([
            'shoot_id' => $shoot->id,
            'user_id' => $photographer->id,
            'role' => 'lead_photographer',
            'payout_agreed' => 25000.00,
        ]);

        PhotographerPayment::create([
            'payment_number' => 'CPAY-2026-999',
            'shoot_id' => $shoot->id,
            'user_id' => $photographer->id,
            'amount' => 15000.00,
            'payment_method' => 'upi',
            'status' => 'paid',
            'paid_at' => now(),
            'transaction_reference' => 'UPI/1234567890',
        ]);

        $this->actingAs($photographer);

        $response = $this->get(route('photographers.shoots.invoice', $shoot));

        $response->assertStatus(200);
        $response->assertSee('PAYOUT INVOICE');
        $response->assertSee('PINV-2026-999');
        $response->assertSee('Arun Kumar');
        $response->assertSee('Grand Royal Wedding');
        $response->assertSee('25,000'); // Fixed fee
        $response->assertSee('15,000'); // Agency paid
        $response->assertSee('10,000'); // Net balance due
        $response->assertSee('Print / Save as PDF');
    }
}
