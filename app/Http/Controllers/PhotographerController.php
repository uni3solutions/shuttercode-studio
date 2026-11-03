<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\PhotographerPayment;
use App\Models\Shoot;
use App\Models\ShootAssignment;
use App\Models\ShootDelivery;
use App\Models\StudioSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PhotographerController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::where('role', 'photographer')
            ->withCount('assignments')
            ->with(['assignments.shoot']);

        if ($request->filled('specialization')) {
            $query->where('specialization', $request->input('specialization'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('specialization', 'like', "%{$search}%");
            });
        }

        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'name_desc' => $query->orderBy('name', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'shoots_desc' => $query->orderBy('assignments_count', 'desc'),
            'newest' => $query->orderBy('created_at', 'desc')->orderBy('id', 'desc'),
            default => $query->orderBy('created_at', 'desc')->orderBy('id', 'desc'),
        };

        $photographers = $query->paginate(12)->withQueryString();

        $stats = [
            'total' => User::where('role', 'photographer')->count(),
            'active_assignments' => User::where('role', 'photographer')->withCount('assignments')->get()->sum('assignments_count'),
            'active' => User::where('role', 'photographer')->where('status', 'active')->count(),
        ];

        $specializations = User::where('role', 'photographer')
            ->distinct()
            ->pluck('specialization')
            ->filter()
            ->values();

        return view('photographers.index', compact('photographers', 'stats', 'specializations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:50'],
            'specialization' => ['nullable', 'string', 'max:150'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $photographer = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'specialization' => ! empty($validated['specialization']) ? $validated['specialization'] : 'Candid Photographer',
            'role' => 'photographer',
            'password' => Hash::make(! empty($validated['password']) ? $validated['password'] : 'password'),
            'status' => 'active',
        ]);

        ActivityLog::log('photographer_created', "Photographer {$photographer->name} added to studio roster.", $photographer);

        return back()->with('success', "Photographer {$photographer->name} added successfully!");
    }

    public function destroy(User $photographer): RedirectResponse
    {
        $name = $photographer->name;
        $photographer->delete();

        ActivityLog::log('photographer_deleted', "Photographer {$name} removed from studio roster.");

        return back()->with('success', "Photographer {$name} removed successfully.");
    }

    /**
     * Dedicated Photographer Dashboard with live metrics & overview.
     */
    public function dashboard(): View
    {
        $user = auth()->user() ?? User::where('role', 'photographer')->first();
        $today = now()->toDateString();

        $assignments = ShootAssignment::with(['shoot.customer', 'shoot.delivery'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Financials
        $totalLifetimeIncome = (float) PhotographerPayment::where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('amount');

        $totalAgreed = (float) $assignments->sum('payout_agreed');
        $totalPendingBalance = max(0, $totalAgreed - $totalLifetimeIncome);

        // Assign paid & balance to each assignment
        $assignments->each(function ($assignment) use ($user) {
            $assignment->amount_paid = (float) PhotographerPayment::where('user_id', $user->id)
                ->where('shoot_id', $assignment->shoot_id)
                ->where('status', 'paid')
                ->sum('amount');
            $assignment->balance_due = max(0, (float) $assignment->payout_agreed - $assignment->amount_paid);
        });

        // Shoots metrics
        $pendingShootsCount = $assignments->filter(function ($a) {
            return ! in_array($a->shoot?->status, ['completed', 'cancelled'], true);
        })->count();

        $completedShootsCount = $assignments->filter(function ($a) {
            return $a->shoot?->status === 'completed';
        })->count();

        $dataPendingCount = $assignments->filter(function ($a) use ($today) {
            $shoot = $a->shoot;
            if (! $shoot || $shoot->status === 'cancelled') {
                return false;
            }
            $isPastOrToday = $shoot->shoot_date?->toDateString() <= $today;
            $hasNoDrive = empty($shoot->delivery?->raw_drive_link);

            return $isPastOrToday && $hasNoDrive;
        })->count();

        $dataSubmittedPaymentDueCount = $assignments->filter(function ($a) {
            $shoot = $a->shoot;
            if (! $shoot) {
                return false;
            }
            $hasDrive = ! empty($shoot->delivery?->raw_drive_link);

            return $hasDrive && $a->balance_due > 0;
        })->count();

        $recentShoots = $assignments->take(4);

        $recentPayments = PhotographerPayment::with('shoot')
            ->where('user_id', $user->id)
            ->orderBy('paid_at', 'desc')
            ->take(5)
            ->get();

        $studioSettings = StudioSetting::current();

        return view('photographers.dashboard', compact(
            'user',
            'totalLifetimeIncome',
            'totalPendingBalance',
            'totalAgreed',
            'pendingShootsCount',
            'completedShootsCount',
            'dataPendingCount',
            'dataSubmittedPaymentDueCount',
            'recentShoots',
            'recentPayments',
            'studioSettings'
        ));
    }

    /**
     * Categorized Assigned Shoots Portal.
     */
    public function portal(Request $request): View
    {
        $user = auth()->user() ?? User::where('role', 'photographer')->first();
        $today = now()->toDateString();

        $allAssignments = ShootAssignment::with(['shoot.customer', 'shoot.delivery'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Compute paid & balance per assignment
        $allAssignments->each(function ($assignment) use ($user) {
            $assignment->amount_paid = (float) PhotographerPayment::where('user_id', $user->id)
                ->where('shoot_id', $assignment->shoot_id)
                ->where('status', 'paid')
                ->sum('amount');
            $assignment->balance_due = max(0, (float) $assignment->payout_agreed - $assignment->amount_paid);
        });

        // Totals
        $totalAgreed = (float) $allAssignments->sum('payout_agreed');
        $totalPaid = (float) PhotographerPayment::where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('amount');
        $balanceLeft = max(0, $totalAgreed - $totalPaid);

        // Counts for tabs
        $counts = [
            'all' => $allAssignments->count(),
            'today' => $allAssignments->filter(fn ($a) => $a->shoot?->shoot_date?->toDateString() === $today)->count(),
            'upcoming' => $allAssignments->filter(fn ($a) => $a->shoot?->shoot_date?->toDateString() > $today)->count(),
            'data_pending' => $allAssignments->filter(function ($a) use ($today) {
                $shoot = $a->shoot;
                if (! $shoot || $shoot->status === 'cancelled') {
                    return false;
                }

                return $shoot->shoot_date?->toDateString() <= $today && empty($shoot->delivery?->raw_drive_link);
            })->count(),
            'payment_due' => $allAssignments->filter(function ($a) {
                return ! empty($a->shoot?->delivery?->raw_drive_link) && $a->balance_due > 0;
            })->count(),
            'completed' => $allAssignments->filter(fn ($a) => $a->shoot?->status === 'completed')->count(),
        ];

        // Filter assignments based on query tab
        $activeCategory = $request->query('category', 'all');

        $assignments = match ($activeCategory) {
            'today' => $allAssignments->filter(fn ($a) => $a->shoot?->shoot_date?->toDateString() === $today),
            'upcoming' => $allAssignments->filter(fn ($a) => $a->shoot?->shoot_date?->toDateString() > $today),
            'data_pending' => $allAssignments->filter(function ($a) use ($today) {
                $shoot = $a->shoot;
                if (! $shoot || $shoot->status === 'cancelled') {
                    return false;
                }

                return $shoot->shoot_date?->toDateString() <= $today && empty($shoot->delivery?->raw_drive_link);
            }),
            'payment_due' => $allAssignments->filter(function ($a) {
                return ! empty($a->shoot?->delivery?->raw_drive_link) && $a->balance_due > 0;
            }),
            'completed' => $allAssignments->filter(fn ($a) => $a->shoot?->status === 'completed'),
            default => $allAssignments,
        };

        $studioSettings = StudioSetting::current();

        return view('photographers.portal', compact(
            'user',
            'assignments',
            'counts',
            'activeCategory',
            'totalAgreed',
            'totalPaid',
            'balanceLeft',
            'studioSettings'
        ));
    }

    /**
     * Dedicated Payouts & Invoices History.
     */
    public function payments(Request $request): View
    {
        $user = auth()->user() ?? User::where('role', 'photographer')->first();

        // All assignments for this photographer
        $assignments = ShootAssignment::with(['shoot.customer', 'shoot.delivery'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $assignments->each(function ($assignment) use ($user) {
            $shootPayments = PhotographerPayment::where('user_id', $user->id)
                ->where('shoot_id', $assignment->shoot_id)
                ->where('status', 'paid')
                ->orderBy('paid_at', 'desc')
                ->get();

            $assignment->amount_paid = (float) $shootPayments->sum('amount');
            $assignment->balance_due = max(0, (float) $assignment->payout_agreed - $assignment->amount_paid);
            $assignment->latest_payment = $shootPayments->first();
        });

        // All payment disbursements
        $payments = PhotographerPayment::with(['shoot.customer'])
            ->where('user_id', $user->id)
            ->orderBy('paid_at', 'desc')
            ->get();

        $totalLifetimeIncome = (float) $payments->where('status', 'paid')->sum('amount');
        $totalAgreed = (float) $assignments->sum('payout_agreed');
        $totalPendingBalance = max(0, $totalAgreed - $totalLifetimeIncome);

        $studioSettings = StudioSetting::current();

        return view('photographers.payments', compact(
            'user',
            'assignments',
            'payments',
            'totalLifetimeIncome',
            'totalAgreed',
            'totalPendingBalance',
            'studioSettings'
        ));
    }

    /**
     * Show official printable Photographer Remuneration Invoice.
     */
    public function invoice(Shoot $shoot): View
    {
        $user = auth()->user() ?? User::where('role', 'photographer')->first();

        // Authorize: ensure this photographer is assigned to this shoot
        $assignment = ShootAssignment::where('shoot_id', $shoot->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $shoot->load(['customer', 'delivery']);

        // Payments disbursed to photographer for this shoot
        $payments = PhotographerPayment::where('shoot_id', $shoot->id)
            ->where('user_id', $user->id)
            ->orderBy('paid_at', 'asc')
            ->get();

        $amountPaid = (float) $payments->where('status', 'paid')->sum('amount');
        $balanceDue = max(0, (float) $assignment->payout_agreed - $amountPaid);

        $studioSettings = StudioSetting::current();

        $invoiceNumber = 'PINV-'.substr($shoot->shoot_number, 4).'-'.str_pad($assignment->id, 2, '0', STR_PAD_LEFT);
        $amountInWords = Invoice::formatAmountInWords((float) $assignment->payout_agreed);

        return view('photographers.invoice', compact(
            'user',
            'shoot',
            'assignment',
            'payments',
            'amountPaid',
            'balanceDue',
            'studioSettings',
            'invoiceNumber',
            'amountInWords'
        ));
    }

    public function submitDelivery(Request $request, Shoot $shoot): RedirectResponse
    {
        $user = auth()->user();

        // Authorize: ensure photographer is assigned to this shoot
        if ($user && $user->isPhotographer()) {
            $isAssigned = ShootAssignment::where('shoot_id', $shoot->id)
                ->where('user_id', $user->id)
                ->exists();

            if (! $isAssigned) {
                abort(403, 'Unauthorized. You are not assigned to this shoot.');
            }
        }

        $validated = $request->validate([
            'raw_photos_count' => ['nullable', 'integer', 'min:0'],
            'raw_drive_link' => ['nullable', 'url', 'max:500'],
            'notes' => ['nullable', 'string'],
        ]);

        $delivery = ShootDelivery::firstOrNew(['shoot_id' => $shoot->id]);
        $delivery->raw_photos_count = $validated['raw_photos_count'] ?? $delivery->raw_photos_count;
        $delivery->raw_drive_link = $validated['raw_drive_link'] ?? $delivery->raw_drive_link;
        $delivery->notes = $validated['notes'] ?? $delivery->notes;
        $delivery->submitted_by = auth()->id() ?? 1;

        if ($delivery->raw_drive_link && in_array($shoot->status, ['scheduled', 'crew_assigned', 'in_progress'], true)) {
            $shoot->status = 'raw_uploaded';
            $delivery->delivery_status = 'raw_uploaded';
            $shoot->save();
        }

        $delivery->save();

        ActivityLog::log('delivery_submitted', "Photographer submitted RAW drive files for shoot {$shoot->shoot_number}.", $shoot);

        return back()->with('success', 'RAW Drive link submitted to studio team successfully!');
    }
}
