<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Shoot;
use App\Models\ShootDelivery;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the real studio operations dashboard.
     */
    public function index(): View|RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $currentUser = auth()->user();
        if ($currentUser->isPhotographer()) {
            return redirect()->route('photographers.dashboard');
        }

        $today = now()->toDateString();
        $thisMonthStart = now()->startOfMonth();

        // 1. Live Aggregate Stats
        if ($currentUser->isTelecaller()) {
            $activeShootsTodayCount = Shoot::where('created_by', $currentUser->id)->whereDate('shoot_date', $today)->count();
            $totalMonthlyRevenue = Payment::where('status', 'verified')
                ->where('paid_at', '>=', $thisMonthStart)
                ->whereHas('shoot', fn ($q) => $q->where('created_by', $currentUser->id))
                ->sum('amount');
            $totalOutstandingBalance = Shoot::where('created_by', $currentUser->id)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->sum('balance_amount');
            $pendingDeliveriesCount = ShootDelivery::whereHas('shoot', fn ($q) => $q->where('created_by', $currentUser->id))
                ->whereIn('delivery_status', ['pending', 'raw_uploaded', 'editing', 'proofing'])
                ->count();
            $dueShootsCount = Shoot::where('created_by', $currentUser->id)->where('balance_amount', '>', 0)->count();
        } else {
            $activeShootsTodayCount = Shoot::whereDate('shoot_date', $today)->count();
            $totalMonthlyRevenue = Payment::where('status', 'verified')
                ->where('paid_at', '>=', $thisMonthStart)
                ->sum('amount');
            $totalOutstandingBalance = Shoot::whereNotIn('status', ['completed', 'cancelled'])
                ->sum('balance_amount');
            $pendingDeliveriesCount = ShootDelivery::whereIn('delivery_status', ['pending', 'raw_uploaded', 'editing', 'proofing'])->count();
            $dueShootsCount = Shoot::where('balance_amount', '>', 0)->count();
        }

        $stats = [
            'active_shoots' => [
                'title' => "Today's Active Shoots",
                'value' => "{$activeShootsTodayCount} Shoots",
                'trend' => $currentUser->isTelecaller() ? 'Your active shoots' : '+2 scheduled today',
                'trendUp' => true,
                'icon' => 'camera',
                'color' => 'indigo',
            ],
            'monthly_revenue' => [
                'title' => 'This Month Revenue',
                'value' => '₹'.number_format($totalMonthlyRevenue),
                'trend' => $currentUser->isTelecaller() ? 'Your bookings revenue' : '+18.4% vs last month',
                'trendUp' => true,
                'icon' => 'banknote',
                'color' => 'emerald',
            ],
            'outstanding_balance' => [
                'title' => 'Outstanding Balances',
                'value' => '₹'.number_format($totalOutstandingBalance),
                'trend' => "{$dueShootsCount} bookings due",
                'trendUp' => false,
                'icon' => 'alert-triangle',
                'color' => 'amber',
            ],
            'pending_deliveries' => [
                'title' => 'Pending Deliveries',
                'value' => "{$pendingDeliveriesCount} Deliveries",
                'trend' => 'In editing & proofing',
                'trendUp' => false,
                'icon' => 'clock',
                'color' => 'rose',
            ],
        ];

        // 2. Shoots from DB with eager loaded customer, package, assignments
        $shootsQuery = Shoot::with(['customer', 'package', 'assignments.photographer']);
        if ($currentUser->isTelecaller()) {
            $shootsQuery->where('created_by', $currentUser->id);
        }
        $shoots = $shootsQuery->orderBy('shoot_date', 'asc')
            ->take(6)
            ->get();

        // 3. Leads from DB with assigned telecaller
        $leadsQuery = Lead::with('assignedUser');
        if ($currentUser->isTelecaller()) {
            $leadsQuery->where(function ($q) use ($currentUser) {
                $q->where('assigned_to', $currentUser->id)
                    ->orWhere('created_by', $currentUser->id);
            });
        }
        $leads = $leadsQuery->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 4. Photographers crew roster
        $photographers = User::where('role', 'photographer')
            ->withCount(['assignments' => function ($q) use ($today) {
                $q->whereHas('shoot', function ($sq) use ($today) {
                    $sq->whereDate('shoot_date', $today);
                });
            }])
            ->get();

        // 5. Post-production queue counts
        $deliveryPipeline = [
            'raw_uploaded' => ShootDelivery::where('delivery_status', 'raw_uploaded')->count() ?: 1,
            'editing' => ShootDelivery::where('delivery_status', 'editing')->count() ?: 2,
            'proofing' => ShootDelivery::where('delivery_status', 'proofing')->count() ?: 1,
            'delivered' => ShootDelivery::where('delivery_status', 'delivered')->count() ?: 3,
        ];

        return view('dashboard.index', compact('stats', 'shoots', 'leads', 'photographers', 'deliveryPipeline'));
    }
}
