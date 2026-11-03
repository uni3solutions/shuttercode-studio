@extends('layouts.app', [
    'title' => $shoot->shoot_number . ' — ' . $shoot->title,
    'breadcrumbs' => ['Home' => '/', 'Shoots' => route('shoots.index'), $shoot->shoot_number => '#']
])

@section('content')
<div class="space-y-6">
    <!-- Shoot Command Center Header -->
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 border border-indigo-100 text-[#4F46E5] font-bold text-xl flex items-center justify-center shrink-0">
                <x-icon name="camera" class="w-7 h-7" />
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="font-mono text-xs font-bold text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded border border-indigo-100">{{ $shoot->shoot_number }}</span>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">{{ $shoot->title }}</h2>
                </div>
                <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-slate-500 mt-1">
                    <span class="font-semibold text-slate-800">{{ $shoot->event_type }} &bull; {{ $shoot->package?->name ?? 'Custom Package' }}</span>
                    <span>&bull;</span>
                    <span class="flex items-center gap-1">
                        <x-icon name="calendar" class="w-3.5 h-3.5 text-slate-400" />
                        {{ $shoot->shoot_date->format('d M Y') }}
                        @if($shoot->start_time) ({{ date('h:i A', strtotime($shoot->start_time)) }}) @endif
                    </span>
                    <span>&bull;</span>
                    <span class="flex items-center gap-1">
                        <x-icon name="map-pin" class="w-3.5 h-3.5 text-slate-400" />
                        {{ $shoot->venue }}
                    </span>
                    @php $attribution = $shoot->getTelecallerAttribution(); @endphp
                    @if($attribution['user'])
                        <span>&bull;</span>
                        <span class="inline-flex items-center gap-1 font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200/60 text-[11px]" title="{{ $attribution['label'] }}: {{ $attribution['user']->name }}">
                            <x-icon name="headset" class="w-3 h-3 text-amber-500" />
                            <span>{{ $attribution['label'] }}: <strong class="font-semibold">{{ $attribution['user']->name }}</strong></span>
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Workflow Status & Actions -->
        <div class="flex flex-wrap items-center gap-2.5">
            <form action="{{ route('shoots.status', $shoot) }}" method="POST" class="flex items-center gap-1.5">
                @csrf
                <select name="status" onchange="this.form.submit()" class="text-xs bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 font-semibold text-slate-700 focus:outline-hidden">
                    @foreach(['scheduled', 'crew_assigned', 'in_progress', 'raw_uploaded', 'editing', 'client_review', 'completed', 'cancelled'] as $st)
                        <option value="{{ $st }}" @selected($shoot->status === $st)>
                            Status: {{ ucwords(str_replace('_', ' ', $st)) }}
                        </option>
                    @endforeach
                </select>
            </form>

            <x-button variant="secondary" size="sm" icon="pencil" href="{{ route('shoots.edit', $shoot) }}">
                Edit
            </x-button>
            <x-button variant="primary" size="sm" icon="receipt" onclick="document.getElementById('modal-payment').classList.remove('hidden')">
                Record Payment
            </x-button>
        </div>
    </div>

    <!-- Production Pipeline Visual Stepper -->
    @php
        $pipelineSteps = [
            'scheduled' => 'Scheduled',
            'crew_assigned' => 'Crew Assigned',
            'in_progress' => 'Shoot Day',
            'raw_uploaded' => 'RAW Backup',
            'editing' => 'Editing',
            'completed' => 'Delivered'
        ];
        $stepKeys = array_keys($pipelineSteps);
        $currentIdx = array_search($shoot->status, $stepKeys);
        if ($currentIdx === false) $currentIdx = 0;
    @endphp
    <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-2xs overflow-x-auto">
        <div class="flex items-center justify-between min-w-[600px] text-xs">
            @foreach($pipelineSteps as $k => $stepTitle)
                @php $idx = array_search($k, $stepKeys); @endphp
                <div class="flex items-center gap-2 {{ $idx <= $currentIdx ? 'text-[#4F46E5] font-bold' : 'text-slate-400' }}">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-[11px] {{ $idx < $currentIdx ? 'bg-emerald-500 text-white' : ($idx === $currentIdx ? 'bg-[#4F46E5] text-white ring-4 ring-indigo-50' : 'bg-slate-100 text-slate-500') }}">
                        @if($idx < $currentIdx)
                            <x-icon name="check" class="w-3.5 h-3.5" />
                        @else
                            {{ $idx + 1 }}
                        @endif
                    </div>
                    <span>{{ $stepTitle }}</span>
                </div>
                @if(!$loop->last)
                    <div class="flex-1 h-0.5 mx-2 {{ $idx < $currentIdx ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Main Grid: 2-Column Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Logistics, Crew & Financials -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Logistics & Client Info -->
            <x-card title="Client & Shoot Logistics">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400">Client Contact:</span>
                        <div class="font-bold text-slate-900 text-sm mt-0.5">{{ $shoot->customer?->name }}</div>
                        <div class="font-mono text-slate-600 mt-0.5">{{ $shoot->customer?->phone }}</div>
                        @if($shoot->customer?->email)
                            <div class="text-slate-500">{{ $shoot->customer->email }}</div>
                        @endif
                    </div>

                    <div>
                        <span class="text-slate-400">Venue & Map Navigation:</span>
                        <div class="font-bold text-slate-900 mt-0.5">{{ $shoot->venue }}</div>
                        @if($shoot->location_url)
                            <a href="{{ $shoot->location_url }}" target="_blank" class="inline-flex items-center gap-1 text-[#4F46E5] font-semibold hover:underline mt-1">
                                <x-icon name="map-pin" class="w-3.5 h-3.5" />
                                <span>Open in Google Maps &rarr;</span>
                            </a>
                        @endif
                    </div>

                    @if($shoot->requirements)
                        <div class="sm:col-span-2 pt-2 border-t border-slate-100">
                            <span class="text-slate-400">Special Requirements & Deliverables:</span>
                            <pre class="font-sans text-xs text-slate-700 whitespace-pre-line mt-1 bg-slate-50 p-3 rounded-lg border border-slate-100">{{ $shoot->requirements }}</pre>
                        </div>
                    @endif
                </div>
            </x-card>

            <!-- Crew Assigned Section -->
            <x-card>
                <x-slot:title>
                    <div class="flex items-center gap-2">
                        <span>Assigned Crew Members</span>
                        <x-badge variant="info" size="xs">{{ $shoot->assignments->count() }} Artists</x-badge>
                    </div>
                </x-slot:title>
                <x-slot:action>
                    <x-button variant="secondary" size="xs" icon="user-plus" onclick="document.getElementById('modal-crew').classList.remove('hidden')">
                        Assign Crew
                    </x-button>
                </x-slot:action>

                <!-- Requirements Tracker Bar -->
                <div class="p-3 mb-3 rounded-xl bg-slate-50 border border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="text-slate-500 font-semibold uppercase text-[10px] tracking-wider">Required Crew:</span>
                        <span class="px-2 py-0.5 rounded-md font-semibold {{ $assignedPhotographers >= $reqPhotographers ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                            📷 {{ $assignedPhotographers }}/{{ $reqPhotographers }} Photographers
                        </span>
                        @if($reqVideographers > 0)
                            <span class="px-2 py-0.5 rounded-md font-semibold {{ $assignedVideographers >= $reqVideographers ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                🎥 {{ $assignedVideographers }}/{{ $reqVideographers }} Videographers
                            </span>
                        @endif
                        @if($reqDrone)
                            <span class="px-2 py-0.5 rounded-md font-semibold {{ $assignedDrone > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                🛸 {{ $assignedDrone > 0 ? '1 Drone Pilot Assigned' : '1 Drone Pilot Needed' }}
                            </span>
                        @endif
                    </div>
                    @if($assignedPhotographers >= $reqPhotographers && ($reqVideographers == 0 || $assignedVideographers >= $reqVideographers) && (! $reqDrone || $assignedDrone > 0))
                        <span class="text-[11px] font-bold text-emerald-600 flex items-center gap-1">
                            <x-icon name="check-circle" class="w-3.5 h-3.5" />
                            <span>All Requirements Fulfilled</span>
                        </span>
                    @else
                        <span class="text-[11px] font-semibold text-amber-600 flex items-center gap-1">
                            <x-icon name="alert-circle" class="w-3.5 h-3.5" />
                            <span>Crew Slots Pending</span>
                        </span>
                    @endif
                </div>

                <div class="space-y-3">
                    @forelse($shoot->assignments as $assignment)
                        <div class="flex items-center justify-between p-3 rounded-xl border border-slate-200/80 hover:bg-slate-50 transition-colors text-xs">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs">
                                    {{ substr($assignment->photographer?->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">{{ $assignment->photographer?->name }}</div>
                                    <div class="text-slate-500 capitalize">{{ str_replace('_', ' ', $assignment->role) }} &bull; {{ $assignment->photographer?->phone }}</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <span class="text-[11px] text-slate-400">Agreed Payout</span>
                                    <div class="font-bold text-slate-800">₹{{ number_format($assignment->payout_agreed) }}</div>
                                </div>
                                <form action="{{ route('shoots.crew.remove', [$shoot, $assignment]) }}" method="POST" onsubmit="return confirm('Remove crew assignment?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-md transition-colors" title="Remove assignment">
                                        <x-icon name="trash-2" class="w-3.5 h-3.5" />
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-slate-400 text-xs">
                            <x-icon name="user-check" class="w-6 h-6 mx-auto text-slate-300 mb-1" />
                            <p>No crew assigned yet. Click "Assign Crew" to allocate team members.</p>
                        </div>
                    @endforelse
                </div>
            </x-card>

            <!-- Payment Ledger for Shoot -->
            <x-card :noPadding="true">
                <x-slot:title>
                    <span>Customer Payment Transactions</span>
                </x-slot:title>
                <x-slot:action>
                    <x-button variant="primary" size="xs" icon="plus" onclick="document.getElementById('modal-payment').classList.remove('hidden')">
                        Add Payment
                    </x-button>
                </x-slot:action>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-4 first:pl-5">Receipt No</th>
                                <th class="py-3 px-4">Invoice</th>
                                <th class="py-3 px-4">Amount</th>
                                <th class="py-3 px-4">Method</th>
                                <th class="py-3 px-4">Type</th>
                                <th class="py-3 px-4">Reference</th>
                                <th class="py-3 px-4">Date</th>
                                <th class="py-3 px-4 text-right pr-5">Payment Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($shoot->payments as $payment)
                                @php
                                    $inv = $payment->invoice ?? $shoot->invoices->first();
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-3 px-4 first:pl-5 font-mono font-medium text-slate-800">
                                        {{ $payment->payment_number }}
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($inv)
                                            <a href="{{ route('invoices.show', $inv) }}" 
                                               class="inline-flex items-center gap-1 font-mono text-[11px] font-semibold text-[#4F46E5] hover:text-[#4338CA] bg-indigo-50/80 hover:bg-indigo-100 px-2 py-0.5 rounded border border-indigo-200/80 transition-colors"
                                               title="View Tax Invoice {{ $inv->invoice_number }}">
                                                <x-icon name="file-text" class="w-3 h-3 text-[#4F46E5]" />
                                                <span>{{ $inv->invoice_number }}</span>
                                            </a>
                                        @else
                                            <a href="{{ route('invoices.create', ['shoot_id' => $shoot->id]) }}" 
                                               class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-500 hover:text-indigo-600 bg-slate-100 hover:bg-indigo-50 px-2 py-0.5 rounded border border-slate-200 transition-colors">
                                                <x-icon name="plus" class="w-3 h-3" />
                                                <span>Generate</span>
                                            </a>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 font-bold text-slate-900">
                                        ₹{{ number_format($payment->amount) }}
                                    </td>
                                    <td class="py-3 px-4 uppercase font-mono text-slate-600">
                                        {{ $payment->payment_method }}
                                    </td>
                                    <td class="py-3 px-4 capitalize text-slate-600">
                                        {{ str_replace('_', ' ', $payment->payment_type) }}
                                    </td>
                                    <td class="py-3 px-4 font-mono text-[11px] text-slate-400">
                                        {{ $payment->transaction_reference ?? '—' }}
                                    </td>
                                    <td class="py-3 px-4 text-slate-500">
                                        {{ $payment->paid_at->format('d M Y, h:i A') }}
                                    </td>
                                    <td class="py-3 px-4 text-right pr-5">
                                        @if($payment->status !== 'verified')
                                            <x-badge variant="danger" size="xs">{{ ucfirst($payment->status) }}</x-badge>
                                        @elseif($shoot->balance_amount <= 0)
                                            <div class="inline-flex flex-col items-end">
                                                <x-badge variant="success" size="xs" :dot="true">Fully Paid</x-badge>
                                                <span class="text-[10px] font-medium text-emerald-600 mt-0.5 whitespace-nowrap">Zero Balance</span>
                                            </div>
                                        @else
                                            <div class="inline-flex flex-col items-end">
                                                <x-badge variant="warning" size="xs" :dot="true">Partial</x-badge>
                                                <span class="text-[11px] font-bold text-amber-700 mt-0.5 whitespace-nowrap">
                                                    Remaining: ₹{{ number_format($shoot->balance_amount) }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-6 text-center text-slate-400">
                                        No payments recorded yet for this shoot.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <!-- Right Column: Financial Breakdown & Delivery Links -->
        <div class="space-y-6">
            <!-- Financial Summary Box -->
            <x-card title="Financial Summary">
                <div class="space-y-3 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Agreed Shoot Package:</span>
                        <span class="font-semibold text-slate-900">₹{{ number_format($shoot->total_amount) }}</span>
                    </div>

                    @if($shoot->discount_amount > 0)
                        <div class="flex justify-between py-1 border-b border-slate-100 text-emerald-600 font-medium">
                            <span>Special Discount:</span>
                            <span>-₹{{ number_format($shoot->discount_amount) }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Total Verified Paid:</span>
                        <span class="font-bold text-emerald-600">₹{{ number_format($shoot->paid_amount) }}</span>
                    </div>

                    <div class="flex justify-between py-1.5 bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                        <span class="font-semibold text-slate-700">Remaining Balance Due:</span>
                        <span class="font-bold {{ $shoot->balance_amount > 0 ? 'text-amber-600 text-sm' : 'text-emerald-600' }}">
                            ₹{{ number_format($shoot->balance_amount) }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    @if($inv = $shoot->invoices->first())
                        <span class="text-xs text-slate-500">Tax Invoice:</span>
                        <a href="{{ route('invoices.show', $inv) }}" class="text-xs font-semibold text-[#4F46E5] hover:underline flex items-center gap-1">
                            <x-icon name="file-text" class="w-3.5 h-3.5" />
                            <span>{{ $inv->invoice_number }} (View / Print) &rarr;</span>
                        </a>
                    @else
                        <a href="{{ route('invoices.create', ['shoot_id' => $shoot->id]) }}" class="text-xs font-semibold text-[#4F46E5] hover:underline flex items-center gap-1">
                            <x-icon name="file-text" class="w-3.5 h-3.5" />
                            <span>Generate Tax Invoice PDF &rarr;</span>
                        </a>
                    @endif
                </div>
            </x-card>

        </div>
    </div>
</div>

<!-- Modal: Assign Crew Member -->
<x-modal id="modal-crew" title="Assign Photographer / Crew" subtitle="Allocate crew according to shoot requirements ({{ $reqPhotographers }} Photo, {{ $reqVideographers }} Video, {{ $reqDrone ? 'Drone' : 'No Drone' }})">
    <form action="{{ route('shoots.crew.assign', $shoot) }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <x-select label="Role for this Shoot (Filtered by Requirement)" name="role" required>
                @foreach($allowedRoles as $roleKey => $roleLabel)
                    <option value="{{ $roleKey }}">{{ $roleLabel }}</option>
                @endforeach
            </x-select>
        </div>

        <div>
            <x-select label="Select Team Artist" name="user_id" required>
                @foreach($photographers as $ph)
                    @php
                        $spec = $ph->specialization ?? 'Photographer';
                    @endphp
                    <option value="{{ $ph->id }}">
                        {{ $ph->name }} ({{ $spec }})
                    </option>
                @endforeach
            </x-select>
        </div>

        <div>
            <x-input 
                label="Agreed Crew Payout (₹)" 
                name="payout_agreed" 
                type="number" 
                step="0.01" 
                placeholder="e.g. 15000" 
                required 
            />
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Assignment Instructions</label>
            <textarea 
                name="notes" 
                rows="2" 
                placeholder="Specific gear requirements, reporting time..."
                class="w-full text-xs rounded-lg border border-slate-300 p-2.5 placeholder-slate-400 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
            ></textarea>
        </div>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
            <x-button variant="secondary" size="sm" onclick="document.getElementById('modal-crew').classList.add('hidden')">
                Cancel
            </x-button>
            <x-button variant="primary" size="sm" type="submit">
                Confirm Assignment
            </x-button>
        </div>
    </form>
</x-modal>

<!-- Modal: Record Payment -->
<x-modal id="modal-payment" title="Record Customer Payment" subtitle="Add advance or balance installment">
    <form action="{{ route('payments.store') }}" method="POST" class="space-y-4">
        @csrf
        <input type="hidden" name="shoot_id" value="{{ $shoot->id }}">
        <input type="hidden" name="customer_id" value="{{ $shoot->customer_id }}">

        <div>
            <x-input 
                label="Payment Amount (₹)" 
                name="amount" 
                type="number" 
                step="0.01" 
                value="{{ $shoot->balance_amount > 0 ? $shoot->balance_amount : '' }}"
                placeholder="e.g. 25000" 
                required 
            />
        </div>

        <div>
            <x-select label="Payment Method" name="payment_method" required>
                <option value="upi">UPI (GPay / PhonePe / Paytm)</option>
                <option value="bank_transfer">Bank Transfer (NEFT / IMPS)</option>
                <option value="cash">Cash</option>
                <option value="card">Credit / Debit Card</option>
            </x-select>
        </div>

        <div>
            <x-select label="Payment Type" name="payment_type" required>
                <option value="advance">Advance Deposit</option>
                <option value="part_payment">Part Payment Milestone</option>
                <option value="final_payment">Final Balance Payment</option>
            </x-select>
        </div>

        <div>
            <x-input 
                label="Transaction UTR / Reference" 
                name="transaction_reference" 
                placeholder="e.g. UPI/6628901234 or Bank UTR" 
            />
        </div>

        <div>
            <x-input 
                label="Payment Date & Time" 
                name="paid_at" 
                type="datetime-local" 
                value="{{ date('Y-m-d\TH:i') }}" 
                required 
            />
        </div>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
            <x-button variant="secondary" size="sm" onclick="document.getElementById('modal-payment').classList.add('hidden')">
                Cancel
            </x-button>
            <x-button variant="primary" size="sm" type="submit">
                Save & Verify Payment
            </x-button>
        </div>
    </form>
</x-modal>
@endsection
