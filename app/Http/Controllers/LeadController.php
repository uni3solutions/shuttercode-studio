<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\ShootType;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        // Auto-backfill any legacy leads where created_by is null
        if (Lead::whereNull('created_by')->whereNotNull('assigned_to')->exists()) {
            Lead::whereNull('created_by')->whereNotNull('assigned_to')->update([
                'created_by' => DB::raw('assigned_to'),
            ]);
        }

        $query = Lead::with(['assignedUser', 'creator']);

        if ($user?->isTelecaller()) {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhere('created_by', $user->id);
            });
        } else {
            if ($request->filled('created_by')) {
                $telecallerId = (int) $request->input('created_by');
                $query->where(function ($q) use ($telecallerId) {
                    $q->where('created_by', $telecallerId)
                        ->orWhere('assigned_to', $telecallerId);
                });
            }
            if ($request->filled('assigned_to')) {
                $query->where('assigned_to', $request->input('assigned_to'));
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('source')) {
            $query->where('source', $request->input('source'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('lead_number', 'like', "%{$search}%")
                    ->orWhere('event_type', 'like', "%{$search}%")
                    ->orWhere('venue', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('assignedUser', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('creator', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Date-wise / Custom Date Range Filter
        $dateType = in_array($request->input('date_type'), ['event_date', 'created_at'])
            ? $request->input('date_type')
            : 'created_at';

        $datePreset = $request->input('date_preset');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if (! empty($datePreset) && $datePreset !== 'all' && $datePreset !== 'custom') {
            switch ($datePreset) {
                case 'today':
                    $dateFrom = today()->toDateString();
                    $dateTo = today()->toDateString();
                    break;
                case 'yesterday':
                    $dateFrom = today()->subDay()->toDateString();
                    $dateTo = today()->subDay()->toDateString();
                    break;
                case 'this_week':
                    $dateFrom = now()->startOfWeek()->toDateString();
                    $dateTo = now()->endOfWeek()->toDateString();
                    break;
                case 'this_month':
                    $dateFrom = now()->startOfMonth()->toDateString();
                    $dateTo = now()->endOfMonth()->toDateString();
                    break;
                case 'last_month':
                    $dateFrom = now()->subMonth()->startOfMonth()->toDateString();
                    $dateTo = now()->subMonth()->endOfMonth()->toDateString();
                    break;
            }
        } elseif (! empty($dateFrom) || ! empty($dateTo)) {
            $datePreset = 'custom';
        }

        $applyDateFilter = function ($q) use ($dateType, $dateFrom, $dateTo): void {
            if (! empty($dateFrom) && ! empty($dateTo)) {
                if ($dateType === 'created_at') {
                    $q->whereBetween('created_at', [
                        $dateFrom.' 00:00:00',
                        $dateTo.' 23:59:59',
                    ]);
                } else {
                    $q->whereBetween('event_date', [$dateFrom, $dateTo]);
                }
            } elseif (! empty($dateFrom)) {
                if ($dateType === 'created_at') {
                    $q->where('created_at', '>=', $dateFrom.' 00:00:00');
                } else {
                    $q->where('event_date', '>=', $dateFrom);
                }
            } elseif (! empty($dateTo)) {
                if ($dateType === 'created_at') {
                    $q->where('created_at', '<=', $dateTo.' 23:59:59');
                } else {
                    $q->where('event_date', '<=', $dateTo);
                }
            }
        };

        $applyDateFilter($query);

        $leads = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $telecallers = $user?->isTelecaller()
            ? User::where('id', $user->id)->get()
            : User::whereIn('role', ['telecaller', 'admin'])->get();

        // Base query for summary statistics (respecting creator, assigned_to, source, and search filters if applied)
        $statsBase = Lead::query();
        if ($user?->isTelecaller()) {
            $statsBase->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhere('created_by', $user->id);
            });
        } else {
            if ($request->filled('created_by')) {
                $telecallerId = (int) $request->input('created_by');
                $statsBase->where(function ($q) use ($telecallerId) {
                    $q->where('created_by', $telecallerId)
                        ->orWhere('assigned_to', $telecallerId);
                });
            }
            if ($request->filled('assigned_to')) {
                $statsBase->where('assigned_to', $request->input('assigned_to'));
            }
        }

        if ($request->filled('source')) {
            $statsBase->where('source', $request->input('source'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $statsBase->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('lead_number', 'like', "%{$search}%")
                    ->orWhere('event_type', 'like', "%{$search}%")
                    ->orWhere('venue', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('assignedUser', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('creator', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $applyDateFilter($statsBase);

        $leadsTodayCount = (clone $statsBase)->whereDate('created_at', today())->count();
        $convertedCount = (clone $statsBase)->where('status', 'converted')->count();
        $activePipelineCount = (clone $statsBase)->whereIn('status', ['new', 'contacted', 'in_discussion', 'interested', 'follow_up_later'])->count();
        $pipelineValue = (clone $statsBase)->whereIn('status', ['new', 'contacted', 'in_discussion', 'interested', 'follow_up_later'])->sum('estimated_budget') ?? 0;
        $totalLeadsCount = (clone $statsBase)->count();
        $conversionRate = $totalLeadsCount > 0 ? round(($convertedCount / $totalLeadsCount) * 100, 1) : 0;

        $leadStats = [
            [
                'title' => "Today's New Leads",
                'value' => "{$leadsTodayCount} Leads",
                'trend' => $leadsTodayCount > 0 ? "+{$leadsTodayCount} generated today" : 'No new leads today yet',
                'trendUp' => $leadsTodayCount > 0,
                'icon' => 'user-plus',
                'color' => 'indigo',
                'subtitle' => 'Telecaller generation',
            ],
            [
                'title' => 'Active In Follow-up',
                'value' => "{$activePipelineCount} Prospects",
                'trend' => 'In active conversation',
                'trendUp' => true,
                'icon' => 'phone-call',
                'color' => 'sky',
                'subtitle' => 'Under pipeline review',
            ],
            [
                'title' => 'Converted to Bookings',
                'value' => "{$convertedCount} Converted",
                'trend' => "{$conversionRate}% conversion rate",
                'trendUp' => $conversionRate > 0,
                'icon' => 'check-circle',
                'color' => 'emerald',
                'subtitle' => 'Won client bookings',
            ],
            [
                'title' => 'Total Leads',
                'value' => "{$totalLeadsCount} Leads",
                'trend' => $pipelineValue > 0 ? '₹'.number_format($pipelineValue).' potential value' : 'All pipeline enquiries',
                'trendUp' => true,
                'icon' => 'layers',
                'color' => 'amber',
                'subtitle' => 'Pipeline Potential Value',
            ],
        ];

        $statusCounts = [
            '' => $totalLeadsCount,
            'new' => (clone $statsBase)->where('status', 'new')->count(),
            'contacted' => (clone $statsBase)->where('status', 'contacted')->count(),
            'in_discussion' => (clone $statsBase)->where('status', 'in_discussion')->count(),
            'interested' => (clone $statsBase)->where('status', 'interested')->count(),
            'follow_up_later' => (clone $statsBase)->where('status', 'follow_up_later')->count(),
            'not_responding' => (clone $statsBase)->where('status', 'not_responding')->count(),
            'not_interested' => (clone $statsBase)->where('status', 'not_interested')->count(),
            'converted' => $convertedCount,
        ];

        return view('leads.index', compact(
            'leads',
            'telecallers',
            'leadStats',
            'statusCounts',
            'dateType',
            'datePreset',
            'dateFrom',
            'dateTo'
        ));
    }

    public function create(): View
    {
        $telecallers = User::whereIn('role', ['telecaller', 'admin'])->get();
        $shootTypes = ShootType::where('is_active', true)->orderBy('name')->get();

        return view('leads.create', compact('telecallers', 'shootTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'event_type' => ['required', 'string', 'max:100'],
            'event_date' => ['nullable', 'date'],
            'venue' => ['nullable', 'string', 'max:255'],
            'estimated_budget' => ['nullable', 'numeric', 'min:0'],
            'source' => ['required', 'string', 'max:100'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        // Generate Lead Number
        $count = Lead::whereYear('created_at', date('Y'))->count() + 1;
        $validated['lead_number'] = 'LD-'.date('Y').'-'.str_pad($count, 3, '0', STR_PAD_LEFT);

        $user = auth()->user();
        if ($user?->isTelecaller()) {
            $validated['created_by'] = $user->id;
            $validated['assigned_to'] = $user->id;
        } else {
            $validated['created_by'] = $user?->id;
        }

        $lead = Lead::create($validated);

        if (! empty($validated['notes'])) {
            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => auth()->id() ?? $lead->assigned_to,
                'type' => 'note',
                'details' => $validated['notes'],
            ]);
        }

        ActivityLog::log('lead_created', "New enquiry {$lead->lead_number} created for {$lead->customer_name} by ".(auth()->user()?->name ?? 'Staff').'.', $lead);

        return redirect()->route('leads.show', $lead)
            ->with('success', "Lead {$lead->lead_number} recorded successfully.");
    }

    public function show(Lead $lead): View
    {
        $user = auth()->user();
        if ($user?->isTelecaller() && $lead->assigned_to !== $user->id && $lead->created_by !== $user->id) {
            abort(403, 'Unauthorized access to this lead.');
        }

        $lead->load(['assignedUser', 'creator', 'activities.user']);
        $telecallers = $user?->isTelecaller()
            ? User::where('id', $user->id)->get()
            : User::whereIn('role', ['telecaller', 'admin'])->get();

        return view('leads.show', compact('lead', 'telecallers'));
    }

    public function addActivity(Request $request, Lead $lead): RedirectResponse
    {
        $user = auth()->user();
        if ($user?->isTelecaller() && $lead->assigned_to !== $user->id && $lead->created_by !== $user->id) {
            abort(403, 'Unauthorized access to this lead.');
        }

        $validated = $request->validate([
            'type' => ['required', 'string'],
            'details' => ['required', 'string'],
            'next_follow_up_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string'],
        ]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id() ?? 1,
            'type' => $validated['type'],
            'details' => $validated['details'],
            'next_follow_up_at' => $validated['next_follow_up_at'] ?? null,
        ]);

        if (! empty($validated['status'])) {
            $lead->status = $validated['status'];
            $lead->save();
        }

        return back()->with('success', 'Follow-up activity recorded.');
    }

    public function convertToCustomer(Lead $lead): RedirectResponse
    {
        $user = auth()->user();
        if ($user?->isTelecaller() && $lead->assigned_to !== $user->id && $lead->created_by !== $user->id) {
            abort(403, 'Unauthorized access to this lead.');
        }

        $customer = Customer::create([
            'name' => $lead->customer_name,
            'phone' => $lead->phone,
            'email' => $lead->email,
            'city' => $lead->venue ? explode(',', $lead->venue)[0] : 'Bengaluru',
            'notes' => "Converted from enquiry #{$lead->lead_number}. Event: {$lead->event_type}",
            'lead_id' => $lead->id,
        ]);

        $lead->status = 'converted';
        $lead->save();

        ActivityLog::log('lead_converted', "Lead {$lead->lead_number} converted to customer {$customer->name}.", $customer);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Enquiry successfully converted to Customer profile! You can now book their shoot.');
    }

    public function edit(Lead $lead): View
    {
        $user = auth()->user();
        if ($user?->isTelecaller() && $lead->assigned_to !== $user->id && $lead->created_by !== $user->id) {
            abort(403, 'Unauthorized access to this lead.');
        }

        $telecallers = $user?->isTelecaller()
            ? User::where('id', $user->id)->get()
            : User::whereIn('role', ['telecaller', 'admin'])->get();

        $shootTypes = ShootType::where('is_active', true)->orderBy('name')->get();

        return view('leads.edit', compact('lead', 'telecallers', 'shootTypes'));
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $user = auth()->user();
        if ($user?->isTelecaller() && $lead->assigned_to !== $user->id && $lead->created_by !== $user->id) {
            abort(403, 'Unauthorized access to this lead.');
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'event_type' => ['required', 'string', 'max:100'],
            'event_date' => ['nullable', 'date'],
            'venue' => ['nullable', 'string', 'max:255'],
            'estimated_budget' => ['nullable', 'numeric', 'min:0'],
            'source' => ['required', 'string', 'max:100'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($user?->isTelecaller() && empty($validated['assigned_to'])) {
            $validated['assigned_to'] = $user->id;
        }

        $lead->update($validated);

        ActivityLog::log('lead_updated', "Lead {$lead->lead_number} updated.", $lead);

        return redirect()->route('leads.show', $lead)
            ->with('success', "Lead {$lead->lead_number} updated successfully.");
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $user = auth()->user();
        if ($user?->isTelecaller() && $lead->assigned_to !== $user->id && $lead->created_by !== $user->id) {
            abort(403, 'Unauthorized access to this lead.');
        }

        $leadNumber = $lead->lead_number;
        $lead->delete();

        return redirect()->route('leads.index')->with('success', "Lead {$leadNumber} removed.");
    }
}
