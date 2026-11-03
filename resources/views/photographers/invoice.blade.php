@extends('layouts.app', [
    'title' => 'Photographer Remuneration Invoice ' . $invoiceNumber,
    'breadcrumbs' => ['Home' => '/my-dashboard', 'Payouts' => route('photographers.payments'), $invoiceNumber => '#']
])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Action Toolbar (Hidden during print) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 print:hidden">
        <div class="flex items-center gap-2">
            <x-button variant="secondary" size="sm" icon="arrow-left" href="{{ route('photographers.payments') }}">
                <span>Back to Payouts Ledger</span>
            </x-button>
        </div>
        <div class="flex items-center gap-2">
            <x-button variant="primary" size="sm" icon="printer" onclick="window.print()">
                <span>Print / Save as PDF</span>
            </x-button>
        </div>
    </div>

    <!-- Authentic Corporate Remuneration Invoice Sheet -->
    <div id="printable-invoice" class="bg-white border border-slate-200/90 p-8 sm:p-12 shadow-sm text-slate-800 print:p-0 print:border-none print:shadow-none print:m-0 rounded-2xl">
        
        <!-- Header: Studio Identity & Invoice Title -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-8 pb-6 border-b border-slate-200">
            <!-- Studio Details (Billed & Paid By) -->
            <div class="max-w-md">
                @if($studioSettings->logo_url)
                    <img src="{{ $studioSettings->logo_url }}" 
                         alt="{{ $studioSettings->studio_name }}" 
                         class="h-12 w-auto max-w-[220px] object-contain mb-3"
                         style="max-height: 48px; max-width: 220px; width: auto; height: auto; object-fit: contain; display: block; margin-bottom: 0.75rem;">
                @else
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-tight">{{ $studioSettings->studio_name ?: 'ShutterCode Studio' }}</h1>
                    @if($studioSettings->studio_tagline)
                        <p class="text-xs text-slate-500 font-medium mt-0.5">{{ $studioSettings->studio_tagline }}</p>
                    @endif
                @endif

                <div class="text-xs text-slate-600 space-y-1 mt-2.5 leading-relaxed">
                    <p class="text-slate-700">{{ $studioSettings->full_address ?? 'Bengaluru, Karnataka' }}</p>
                    <p>
                        <span class="text-slate-400">GSTIN:</span> <span class="font-semibold text-slate-900 font-mono">{{ $studioSettings->gstin ?: '29AABCS1429B1Z8' }}</span>
                        <span class="mx-1 text-slate-300">&bull;</span>
                        <span class="text-slate-400">SAC Code:</span> <span class="font-mono text-slate-800">{{ $studioSettings->hsn_sac_code ?: '998381' }}</span>
                    </p>
                    <p>
                        <span class="text-slate-400">Phone:</span> <span class="text-slate-700">{{ $studioSettings->phone }}</span>
                        <span class="mx-1 text-slate-300">&bull;</span>
                        <span class="text-slate-400">Email:</span> <span class="text-slate-700">{{ $studioSettings->email }}</span>
                    </p>
                </div>
            </div>

            <!-- Invoice Title & Status -->
            <div class="text-left sm:text-right shrink-0">
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-wide uppercase">PAYOUT INVOICE</h2>
                <p class="text-[10px] font-bold tracking-widest text-slate-400 uppercase mt-1">Crew Remuneration &bull; Original for Artist</p>
                
                <div class="mt-4 text-xs text-slate-600 space-y-1">
                    <div>
                        <span class="text-slate-400">Invoice No:</span> 
                        <span class="font-mono font-bold text-slate-900 ml-1">{{ $invoiceNumber }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400">Settlement:</span>
                        @if($balanceDue <= 0)
                            <span class="ml-1 font-bold text-emerald-700 text-xs uppercase tracking-wide">Fully Paid &bull; Settled</span>
                        @else
                            <span class="ml-1 font-bold text-amber-700 text-xs">Balance Rest: ₹{{ number_format($balanceDue) }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Structured Metadata Strip -->
        <div class="flex flex-wrap items-center justify-between gap-4 py-3 px-4 bg-slate-50 border border-slate-200/90 text-xs my-6 rounded-xl">
            <div class="flex-1 min-w-[120px]">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Invoice Date</span>
                <span class="font-medium text-slate-900">{{ now()->format('d M Y') }}</span>
            </div>
            <div class="flex-1 min-w-[120px]">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Shoot Booking Ref</span>
                <span class="font-mono font-medium text-indigo-600 font-bold">{{ $shoot->shoot_number }}</span>
            </div>
            <div class="flex-1 min-w-[120px]">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Shoot Date</span>
                <span class="font-medium text-slate-900">{{ $shoot->shoot_date?->format('d M Y') ?? '—' }}</span>
            </div>
            <div class="flex-1 min-w-[120px]">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Place of Supply</span>
                <span class="font-medium text-slate-900">{{ $studioSettings->state ?? 'Karnataka' }} (Code: 29)</span>
            </div>
        </div>

        <!-- Party Details: Paid By & Paid To -->
        <div class="flex flex-col sm:flex-row justify-between gap-8 pb-6 border-b border-slate-200 text-xs">
            <!-- Studio Details -->
            <div style="flex: 1 1 50%; min-width: 0;">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Billed &amp; Disbursed By (Agency)</div>
                <div class="text-sm font-bold text-slate-900">{{ $studioSettings->studio_name ?: 'ShutterCode Studio' }}</div>
                <div class="text-slate-600 space-y-0.5 mt-1 leading-relaxed">
                    <p>{{ $studioSettings->full_address ?? 'Studio Operations Office' }}</p>
                    <p><span class="text-slate-400">Contact:</span> {{ $studioSettings->phone }}</p>
                    <p><span class="text-slate-400">Email:</span> {{ $studioSettings->email }}</p>
                </div>
            </div>

            <!-- Artist Details -->
            <div style="flex: 1 1 50%; min-width: 0;">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Service Provider / Artist (Paid To)</div>
                <div class="text-sm font-bold text-slate-900">{{ $user->name }}</div>
                <div class="text-slate-600 space-y-0.5 mt-1 leading-relaxed">
                    <p><span class="text-slate-400">Role:</span> <strong class="capitalize text-slate-800">{{ str_replace('_', ' ', $assignment->role) }}</strong></p>
                    <p><span class="text-slate-400">Specialization:</span> {{ $user->specialization ?? 'Creative Artist' }}</p>
                    <p><span class="text-slate-400">Phone:</span> {{ $user->phone ?? '—' }}</p>
                    <p><span class="text-slate-400">Email:</span> {{ $user->email }}</p>
                </div>
            </div>
        </div>

        <!-- Event Logistics Strip -->
        <div class="py-4 border-b border-slate-200 text-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Shoot Assignment &amp; Venue Details</span>
            <div class="flex flex-wrap items-center justify-between gap-4 p-3 bg-slate-50/80 rounded-xl border border-slate-100">
                <div>
                    <strong class="text-slate-900 text-xs">{{ $shoot->title }}</strong>
                    <p class="text-slate-500 text-[11px] mt-0.5">Customer: {{ $shoot->customer?->name }} &bull; Type: {{ $shoot->event_type }}</p>
                </div>
                <div class="text-left sm:text-right">
                    <span class="text-slate-500 block text-[11px]"><x-icon name="map-pin" class="w-3.5 h-3.5 inline text-rose-500" /> {{ $shoot->venue }}</span>
                    <span class="text-slate-400 text-[10px]">Call Time: {{ $shoot->start_time ? date('h:i A', strtotime($shoot->start_time)) : 'Standard' }}</span>
                </div>
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="py-6 border-b border-slate-200">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-y-2 border-slate-900 text-slate-800 uppercase text-[10px] font-bold">
                        <th class="py-2.5 px-3 text-center w-10">#</th>
                        <th class="py-2.5 px-3">Description of Professional Services &amp; Deliverables</th>
                        <th class="py-2.5 px-3 text-center font-mono w-24">SAC Code</th>
                        <th class="py-2.5 px-3 text-center w-14">Qty</th>
                        <th class="py-2.5 px-3 text-right w-28">Fixed Rate (₹)</th>
                        <th class="py-2.5 px-3 text-right w-32">Total Fee (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    <tr>
                        <td class="py-3 px-3 text-center font-medium text-slate-400">1</td>
                        <td class="py-3 px-3">
                            <span class="font-bold text-slate-900 text-xs">
                                Professional Photography &amp; Visual Coverage Services
                            </span>
                            <p class="text-slate-500 text-[11px] mt-1 leading-relaxed">
                                Assignment as <strong>{{ ucwords(str_replace('_', ' ', $assignment->role)) }}</strong> for {{ $shoot->title }}. High-resolution camera coverage, RAW capture ingest, and digital drive backup submission.
                            </p>
                            @if($assignment->notes)
                                <p class="text-slate-400 text-[10px] mt-1 italic">Instructions: {{ $assignment->notes }}</p>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center font-mono text-slate-600">{{ $studioSettings->hsn_sac_code ?: '998381' }}</td>
                        <td class="py-3 px-3 text-center font-medium">1</td>
                        <td class="py-3 px-3 text-right font-medium text-slate-800">₹{{ number_format($assignment->payout_agreed, 2) }}</td>
                        <td class="py-3 px-3 text-right font-bold text-slate-900">₹{{ number_format($assignment->payout_agreed, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Verified Payments Received Sub-Table (if payments made) -->
        @if($payments->isNotEmpty())
            <div class="py-5 border-b border-slate-200">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                    Agency Disbursements Disbursed for this Shoot
                </div>
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 uppercase text-[9px] font-bold">
                            <th class="py-1.5 px-2">Voucher No</th>
                            <th class="py-1.5 px-2">Disbursed Date</th>
                            <th class="py-1.5 px-2">Payment Mode</th>
                            <th class="py-1.5 px-2">Transaction Ref</th>
                            <th class="py-1.5 px-2 text-right">Amount Disbursed (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-600">
                        @foreach($payments as $p)
                            <tr>
                                <td class="py-1.5 px-2 font-mono font-medium text-slate-800">{{ $p->payment_number }}</td>
                                <td class="py-1.5 px-2">{{ $p->paid_at?->format('d M Y') ?? '—' }}</td>
                                <td class="py-1.5 px-2 uppercase font-mono text-[10px]">{{ $p->payment_method }}</td>
                                <td class="py-1.5 px-2 font-mono text-[11px] text-slate-500">{{ $p->transaction_reference ?? '—' }}</td>
                                <td class="py-1.5 px-2 text-right font-semibold text-emerald-700">₹{{ number_format($p->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Financial Summary Row -->
        <div class="py-6 border-b border-slate-200">
            <div class="flex flex-col sm:flex-row justify-between items-start gap-8">
                <!-- Left: Amount in Words & Status -->
                <div style="flex: 1 1 55%; min-width: 0;" class="space-y-3">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Contract Fee Amount in Words</span>
                        <p class="text-xs font-semibold text-slate-900 italic bg-slate-50 p-3 rounded-xl border border-slate-200/80 leading-relaxed">
                            {{ $amountInWords }}
                        </p>
                    </div>
                    @if($balanceDue <= 0)
                        <div class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-2 rounded-xl border border-emerald-200">
                            &#10003; Settlement Status: Fully Settled &amp; Paid (Zero Balance Due)
                        </div>
                    @else
                        <div class="text-xs font-bold text-amber-800 bg-amber-50 px-3 py-2 rounded-xl border border-amber-200">
                            Outstanding Payout Due to Photographer: ₹{{ number_format($balanceDue, 2) }}
                        </div>
                    @endif
                </div>

                <!-- Right: Calculation Breakdown Table -->
                <div style="flex: 0 0 320px; width: 320px;" class="space-y-1.5 text-xs">
                    <div class="flex justify-between py-1 text-slate-600">
                        <span>Contract Fixed Fee:</span>
                        <span class="font-bold text-slate-900">₹{{ number_format($assignment->payout_agreed, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-1 text-emerald-600">
                        <span>Less: Agency Paid:</span>
                        <span class="font-bold">-₹{{ number_format($amountPaid, 2) }}</span>
                    </div>
                    <div class="border-t-2 border-slate-900 pt-2 flex justify-between text-sm font-black text-slate-900">
                        <span>Net Balance Due:</span>
                        <span class="{{ $balanceDue > 0 ? 'text-amber-700' : 'text-slate-900' }}">₹{{ number_format($balanceDue, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Signatures -->
        <div class="pt-8 flex flex-col sm:flex-row justify-between items-end gap-8 text-xs text-slate-500">
            <div>
                <p class="font-medium text-slate-700">Thank you for your creative excellence.</p>
                <p class="text-[11px] text-slate-400 mt-0.5">This is a system-generated remuneration invoice issued by {{ $studioSettings->studio_name }}.</p>
            </div>

            <div class="text-left sm:text-right border-t border-slate-300 pt-2 min-w-[200px]">
                <p class="font-bold text-slate-800 text-xs">{{ $studioSettings->studio_name }}</p>
                <p class="text-[10px] text-slate-400 uppercase tracking-wider mt-0.5">Authorized Accounts Seal</p>
            </div>
        </div>
    </div>
</div>
@endsection
