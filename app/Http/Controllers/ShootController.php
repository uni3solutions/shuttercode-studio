<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Shoot;
use App\Models\ShootAssignment;
use App\Models\ShootDelivery;
use App\Models\ShootType;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShootController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Shoot::with(['customer', 'package', 'assignments.photographer', 'lead.creator', 'lead.assignedUser', 'creator']);

        if ($user?->isTelecaller()) {
            $query->forTelecaller($user->id);
        } elseif ($request->filled('telecaller_id')) {
            $query->forTelecaller((int) $request->input('telecaller_id'));
        }

        // 1. Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // 2. Comprehensive Multi-attribute Search
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('shoot_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('venue', 'like', "%{$search}%")
                    ->orWhere('event_type', 'like', "%{$search}%")
                    ->orWhere('requirements', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%");
                    })
                    ->orWhereHas('package', function ($pkq) use ($search) {
                        $pkq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('assignments.photographer', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('creator', function ($tcq) use ($search) {
                        $tcq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('lead.assignedUser', function ($tcq) use ($search) {
                        $tcq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('lead.creator', function ($tcq) use ($search) {
                        $tcq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('customer.lead.assignedUser', function ($tcq) use ($search) {
                        $tcq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('customer.lead.creator', function ($tcq) use ($search) {
                        $tcq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // 3. Event Type Filter
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->input('event_type'));
        }

        // 4. Assigned Photographer Filter
        if ($request->filled('photographer_id')) {
            $query->whereHas('assignments', function ($q) use ($request) {
                $q->where('user_id', $request->input('photographer_id'));
            });
        }

        // 5. Package Filter
        if ($request->filled('package_id')) {
            $query->where('package_id', $request->input('package_id'));
        }

        // 6. Payment Due / Paid Filter
        if ($request->filled('payment_status')) {
            if ($request->input('payment_status') === 'due') {
                $query->where('balance_amount', '>', 0);
            } elseif ($request->input('payment_status') === 'paid') {
                $query->where('balance_amount', '<=', 0);
            }
        }

        // 7. Date Range Filter
        if ($request->filled('date_range')) {
            $today = now()->toDateString();
            $range = $request->input('date_range');
            if ($range === 'today') {
                $query->whereDate('shoot_date', $today);
            } elseif ($range === 'this_week') {
                $query->whereBetween('shoot_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()]);
            } elseif ($range === 'this_month') {
                $query->whereBetween('shoot_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]);
            } elseif ($range === 'upcoming') {
                $query->whereDate('shoot_date', '>=', $today);
            } elseif ($range === 'past') {
                $query->whereDate('shoot_date', '<', $today);
            }
        }

        // 8. Sorting
        $sort = $request->input('sort', 'shoot_date_desc');
        switch ($sort) {
            case 'shoot_date_asc':
                $query->orderBy('shoot_date', 'asc');
                break;
            case 'created_at_desc':
                $query->orderBy('created_at', 'desc');
                break;
            case 'total_amount_desc':
                $query->orderBy('total_amount', 'desc');
                break;
            case 'balance_amount_desc':
                $query->orderBy('balance_amount', 'desc');
                break;
            case 'shoot_date_desc':
            default:
                $query->orderBy('shoot_date', 'desc')->orderBy('id', 'desc');
                break;
        }

        $shoots = $query->paginate(15)->withQueryString();

        // 9. Data for Filters & Status Counter Badges
        $baseShootQuery = Shoot::query();
        if ($user?->isTelecaller()) {
            $baseShootQuery->forTelecaller($user->id);
        } elseif ($request->filled('telecaller_id')) {
            $baseShootQuery->forTelecaller((int) $request->input('telecaller_id'));
        }

        $eventTypes = (clone $baseShootQuery)->distinct()->orderBy('event_type')->pluck('event_type')->filter()->values();
        $packages = $user?->isTelecaller()
            ? Package::where('created_by', $user->id)->where('is_active', true)->orderBy('name')->get()
            : Package::where('is_active', true)->orderBy('name')->get();
        $photographers = User::where('role', 'photographer')->orderBy('name')->get();
        $telecallers = User::where('role', 'telecaller')->orderBy('name')->get();

        $statusCounts = [
            'all' => (clone $baseShootQuery)->count(),
            'scheduled' => (clone $baseShootQuery)->where('status', 'scheduled')->count(),
            'crew_assigned' => (clone $baseShootQuery)->where('status', 'crew_assigned')->count(),
            'in_progress' => (clone $baseShootQuery)->where('status', 'in_progress')->count(),
            'raw_uploaded' => (clone $baseShootQuery)->where('status', 'raw_uploaded')->count(),
            'editing' => (clone $baseShootQuery)->where('status', 'editing')->count(),
            'completed' => (clone $baseShootQuery)->where('status', 'completed')->count(),
        ];

        return view('shoots.index', compact('shoots', 'eventTypes', 'packages', 'photographers', 'statusCounts', 'telecallers'));
    }

    public function create(Request $request): View
    {
        $user = auth()->user();
        $customers = Customer::orderBy('name')->get();

        // For telecallers, only show packages they created
        if ($user?->isTelecaller()) {
            $packages = Package::where('created_by', $user->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        } else {
            $packages = Package::where('is_active', true)->orderBy('name')->get();
        }

        $photographers = User::where('role', 'photographer')->orderBy('name')->get();
        $shootTypes = ShootType::where('is_active', true)->orderBy('name')->get();
        $selectedCustomerId = $request->query('customer_id');

        return view('shoots.create', compact('customers', 'packages', 'photographers', 'shootTypes', 'selectedCustomerId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'package_id' => ['nullable', 'exists:packages,id'],
            'title' => ['required', 'string', 'max:255'],
            'event_type' => ['required', 'string', 'max:100'],
            'shoot_date' => ['required', 'date'],
            'start_time' => ['nullable'],
            'end_time' => ['nullable'],
            'venue' => ['required', 'string', 'max:255'],
            'location_url' => ['nullable', 'url', 'max:500'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'advance_amount' => ['nullable', 'numeric', 'min:0'],
            'requirements' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'crew' => ['nullable', 'array'],
            'crew.*.user_id' => ['nullable', 'exists:users,id'],
            'crew.*.role' => ['nullable', 'string'],
            'crew.*.payout_agreed' => ['nullable', 'numeric', 'min:0'],
            'crew.*.notes' => ['nullable', 'string'],
        ]);

        // If telecaller, ensure they can only select packages they created
        if (! empty($validated['package_id']) && auth()->user()?->isTelecaller()) {
            $packageExists = Package::where('id', $validated['package_id'])
                ->where('created_by', auth()->id())
                ->exists();
            if (! $packageExists) {
                return back()->withErrors(['package_id' => 'You can only book shoots with photography packages created by you.'])->withInput();
            }
        }

        // Generate Unique Shoot ID: SHT-YYYY-XXX
        $count = Shoot::whereYear('created_at', date('Y'))->count() + 1;
        $validated['shoot_number'] = 'SHT-'.date('Y').'-'.str_pad($count, 3, '0', STR_PAD_LEFT);
        $validated['created_by'] = auth()->id() ?? 1;

        $discount = $validated['discount_amount'] ?? 0;
        $advance = $validated['advance_amount'] ?? 0;
        $validated['paid_amount'] = 0;
        $validated['balance_amount'] = max(0, $validated['total_amount'] - $discount);
        $validated['status'] = 'scheduled';
        // Resolve and link lead if customer was converted from a lead
        $customer = Customer::find($validated['customer_id']);
        if ($customer?->lead_id) {
            $validated['lead_id'] = $customer->lead_id;
        } elseif ($customer?->phone) {
            $matchedLead = Lead::where('phone', $customer->phone)->latest()->first();
            if ($matchedLead) {
                $validated['lead_id'] = $matchedLead->id;
            }
        }

        $shoot = Shoot::create($validated);

        // Assign crew according to requirements if submitted
        $assignedCount = 0;
        if (! empty($validated['crew'])) {
            foreach ($validated['crew'] as $crewMember) {
                if (! empty($crewMember['user_id']) && ! empty($crewMember['role'])) {
                    ShootAssignment::create([
                        'shoot_id' => $shoot->id,
                        'user_id' => $crewMember['user_id'],
                        'role' => $crewMember['role'],
                        'payout_agreed' => $crewMember['payout_agreed'] ?? 0,
                        'status' => 'assigned',
                        'notes' => $crewMember['notes'] ?? null,
                    ]);
                    $assignedCount++;
                }
            }
        }

        if ($assignedCount > 0) {
            $shoot->status = 'crew_assigned';
            $shoot->save();
        }

        // If an initial advance amount was provided, record it as a verified payment transaction
        if ($advance > 0) {
            $paymentCount = Payment::whereYear('created_at', date('Y'))->count() + 1;
            Payment::create([
                'payment_number' => 'PAY-'.date('Y').'-'.str_pad($paymentCount, 3, '0', STR_PAD_LEFT),
                'shoot_id' => $shoot->id,
                'customer_id' => $shoot->customer_id,
                'amount' => $advance,
                'payment_method' => $request->input('payment_method', 'upi'),
                'payment_type' => 'advance',
                'transaction_reference' => $request->input('transaction_reference'),
                'status' => 'verified',
                'paid_at' => now(),
                'notes' => 'Initial booking advance deposit',
                'recorded_by' => auth()->id() ?? 1,
            ]);
        }

        // Initialize Shoot Delivery record
        ShootDelivery::create([
            'shoot_id' => $shoot->id,
            'client_gallery_link' => 'https://gallery.shuttercode.com/view/'.strtolower($shoot->shoot_number),
            'gallery_pin' => (string) rand(1000, 9999),
            'delivery_status' => 'pending',
        ]);

        ActivityLog::log('shoot_created', "Shoot {$shoot->shoot_number} '{$shoot->title}' booked with {$assignedCount} crew assigned.", $shoot);

        return redirect()->route('shoots.show', $shoot)
            ->with('success', "Shoot booking {$shoot->shoot_number} created successfully!");
    }

    public function show(Shoot $shoot): View
    {
        $user = auth()->user();
        if ($user?->isTelecaller() && ! $shoot->isOwnedOrConvertedBy($user)) {
            abort(403, 'Unauthorized access to this shoot.');
        }

        $shoot->load(['customer', 'package', 'assignments.photographer', 'payments.recordedBy', 'delivery', 'invoices']);
        $photographers = User::where('role', 'photographer')->orderBy('name')->get();

        // Compute requirement specs from package
        $pkg = $shoot->package;
        $reqPhotographers = $pkg?->photographers_count ?? 1;
        $reqVideographers = $pkg?->videographers_count ?? 0;
        $reqDrone = (bool) ($pkg?->drone_included ?? false);

        // Count currently assigned crew
        $assignedPhotographers = $shoot->assignments->filter(fn ($a) => in_array($a->role, ['lead_photographer', 'candid_photographer', 'traditional_photographer']))->count();
        $assignedVideographers = $shoot->assignments->filter(fn ($a) => in_array($a->role, ['cinematographer']))->count();
        $assignedDrone = $shoot->assignments->filter(fn ($a) => $a->role === 'drone_pilot')->count();

        // Allowed roles strictly according to requirements
        $allowedRoles = [];
        if ($reqPhotographers > 0) {
            $allowedRoles['lead_photographer'] = 'Lead Candid Photographer';
            if ($reqPhotographers > 1) {
                $allowedRoles['candid_photographer'] = 'Second Candid Photographer';
            }
            $allowedRoles['traditional_photographer'] = 'Traditional / Stage Photographer';
        }
        if ($reqVideographers > 0) {
            $allowedRoles['cinematographer'] = 'Cinematographer (Video)';
        }
        if ($reqDrone) {
            $allowedRoles['drone_pilot'] = 'Drone Aerial Pilot';
        }
        if (! $pkg || ($reqPhotographers + $reqVideographers) > 2) {
            $allowedRoles['assistant'] = 'Assistant / Lighting';
        }

        return view('shoots.show', compact(
            'shoot',
            'photographers',
            'reqPhotographers',
            'reqVideographers',
            'reqDrone',
            'assignedPhotographers',
            'assignedVideographers',
            'assignedDrone',
            'allowedRoles'
        ));
    }

    public function edit(Shoot $shoot): View
    {
        $user = auth()->user();
        if ($user?->isTelecaller() && ! $shoot->isOwnedOrConvertedBy($user)) {
            abort(403, 'Unauthorized access to this shoot.');
        }

        $customers = Customer::orderBy('name')->get();

        if ($user?->isTelecaller()) {
            $packages = Package::where('is_active', true)
                ->where(function ($q) use ($user, $shoot) {
                    $q->where('created_by', $user->id);
                    if ($shoot->package_id) {
                        $q->orWhere('id', $shoot->package_id);
                    }
                })
                ->orderBy('name')
                ->get();
        } else {
            $packages = Package::where('is_active', true)->orderBy('name')->get();
        }

        $shootTypes = ShootType::where('is_active', true)->orderBy('name')->get();

        return view('shoots.edit', compact('shoot', 'customers', 'packages', 'shootTypes'));
    }

    public function update(Request $request, Shoot $shoot): RedirectResponse
    {
        $user = auth()->user();
        if ($user?->isTelecaller() && ! $shoot->isOwnedOrConvertedBy($user)) {
            abort(403, 'Unauthorized access to this shoot.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'event_type' => ['required', 'string', 'max:100'],
            'shoot_date' => ['required', 'date'],
            'start_time' => ['nullable'],
            'end_time' => ['nullable'],
            'venue' => ['required', 'string', 'max:255'],
            'location_url' => ['nullable', 'url', 'max:500'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'requirements' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'string'],
        ]);

        $shoot->update($validated);
        $shoot->recalculateFinancials();

        ActivityLog::log('shoot_updated', "Shoot {$shoot->shoot_number} updated.", $shoot);

        return redirect()->route('shoots.show', $shoot)
            ->with('success', "Shoot {$shoot->shoot_number} updated.");
    }

    public function assignCrew(Request $request, Shoot $shoot): RedirectResponse
    {
        $user = auth()->user();
        if ($user?->isTelecaller() && ! $shoot->isOwnedOrConvertedBy($user)) {
            abort(403, 'Unauthorized access to this shoot.');
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', 'string'],
            'payout_agreed' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        ShootAssignment::create([
            'shoot_id' => $shoot->id,
            'user_id' => $validated['user_id'],
            'role' => $validated['role'],
            'payout_agreed' => $validated['payout_agreed'] ?? 0,
            'status' => 'assigned',
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($shoot->status === 'scheduled') {
            $shoot->status = 'crew_assigned';
            $shoot->save();
        }

        ActivityLog::log('crew_assigned', "Photographer assigned to shoot {$shoot->shoot_number}.", $shoot);

        return back()->with('success', 'Crew member assigned to shoot.');
    }

    public function removeCrew(Shoot $shoot, ShootAssignment $assignment): RedirectResponse
    {
        $user = auth()->user();
        if ($user?->isTelecaller() && ! $shoot->isOwnedOrConvertedBy($user)) {
            abort(403, 'Unauthorized access to this shoot.');
        }

        $assignment->delete();

        return back()->with('success', 'Crew member assignment removed.');
    }

    public function updateStatus(Request $request, Shoot $shoot): RedirectResponse
    {
        $user = auth()->user();
        if ($user?->isTelecaller() && ! $shoot->isOwnedOrConvertedBy($user)) {
            abort(403, 'Unauthorized access to this shoot.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string'],
        ]);

        $shoot->status = $validated['status'];
        $shoot->save();

        ActivityLog::log('shoot_status_changed', "Shoot {$shoot->shoot_number} status changed to {$shoot->status}.", $shoot);

        return back()->with('success', 'Shoot status updated to '.ucwords(str_replace('_', ' ', $shoot->status)).'.');
    }

    public function destroy(Shoot $shoot): RedirectResponse
    {
        $user = auth()->user();
        if ($user?->isTelecaller() && ! $shoot->isOwnedOrConvertedBy($user)) {
            abort(403, 'Unauthorized access to this shoot.');
        }

        $number = $shoot->shoot_number;
        $shoot->delete();

        return redirect()->route('shoots.index')->with('success', "Shoot {$number} removed.");
    }
}
