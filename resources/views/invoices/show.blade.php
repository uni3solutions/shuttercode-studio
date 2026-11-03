@extends('layouts.app', [
    'title' => 'Tax Invoice ' . $invoice->invoice_number,
    'breadcrumbs' => ['Home' => '/', 'Invoices' => route('invoices.index'), $invoice->invoice_number => '#']
])

@section('content')
@php
    $settings = $settings ?? $invoice->getSettings();
    $attachedPayments = $invoice->payments->isNotEmpty() ? $invoice->payments : ($invoice->shoot?->payments ?? collect());
    $taxHalfRate = number_format($invoice->tax_rate / 2, 1);
    $cgst = round($invoice->tax_amount / 2, 2);
    $sgst = $invoice->tax_amount - $cgst;
    $taxableValue = max(0, $invoice->subtotal - $invoice->discount_amount);
    $upiPayUrl = "upi://pay?pa=" . urlencode($settings->upi_id) . "&pn=" . urlencode($settings->studio_name) . "&am=" . ($invoice->balance_amount > 0 ? $invoice->balance_amount : $invoice->total_amount) . "&cu=INR&tn=" . urlencode($invoice->invoice_number);
@endphp

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Action Toolbar (Hidden during print) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 print:hidden">
        <div class="flex items-center gap-2">
            <x-button variant="secondary" size="sm" icon="arrow-left" href="{{ route('invoices.index') }}">
                <span>Back to Invoices</span>
            </x-button>
            <x-button variant="secondary" size="sm" icon="settings" href="{{ route('settings.index', $invoice->telecaller_id && auth()->user()?->isAdmin() ? ['telecaller_id' => $invoice->telecaller_id] : []) }}">
                <span>Edit {{ $invoice->telecaller ? $invoice->telecaller->name . "'s" : 'Studio' }} Invoice Settings</span>
            </x-button>
        </div>
        <div class="flex items-center gap-2">
            <x-button variant="primary" size="sm" icon="printer" onclick="window.print()">
                <span>Print / Save as PDF</span>
            </x-button>
        </div>
    </div>

    <!-- Authentic Corporate Tax Invoice Sheet -->
    <div id="printable-invoice" class="bg-white border border-slate-200/90 p-8 sm:p-12 shadow-sm text-slate-800 print:p-0 print:border-none print:shadow-none print:m-0">
        
        <!-- Header: Seller Identity & Invoice Metadata -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-8 pb-6 border-b border-slate-200">
            <!-- Studio Details (Billed By) -->
            <div class="max-w-md">
                @if($settings->logo_url)
                    <img src="{{ $settings->logo_url }}" 
                         alt="{{ $settings->studio_name }}" 
                         class="h-12 w-auto max-w-[220px] object-contain mb-3"
                         style="max-height: 48px; max-width: 220px; width: auto; height: auto; object-fit: contain; display: block; margin-bottom: 0.75rem;">
                @else
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-tight">{{ $settings->studio_name }}</h1>
                    @if($settings->studio_tagline)
                        <p class="text-xs text-slate-500 font-medium mt-0.5">{{ $settings->studio_tagline }}</p>
                    @endif
                @endif

                <div class="text-xs text-slate-600 space-y-1 mt-2.5 leading-relaxed">
                    <p class="text-slate-700">{{ $settings->full_address }}</p>
                    <p>
                        <span class="text-slate-400">GSTIN:</span> <span class="font-semibold text-slate-900 font-mono">{{ $settings->gstin }}</span>
                        <span class="mx-1 text-slate-300">&bull;</span>
                        <span class="text-slate-400">SAC Code:</span> <span class="font-mono text-slate-800">{{ $settings->hsn_sac_code }}</span>
                    </p>
                    <p>
                        <span class="text-slate-400">Phone:</span> <span class="text-slate-700">{{ $settings->phone }}</span>
                        <span class="mx-1 text-slate-300">&bull;</span>
                        <span class="text-slate-400">Email:</span> <span class="text-slate-700">{{ $settings->email }}</span>
                    </p>
                </div>
            </div>

            <!-- Tax Invoice Details -->
            <div class="text-left sm:text-right shrink-0">
                <h2 class="text-3xl font-black text-slate-900 tracking-wide uppercase">TAX INVOICE</h2>
                <p class="text-[10px] font-bold tracking-widest text-slate-400 uppercase mt-1">Original for Recipient</p>
                
                <div class="mt-4 text-xs text-slate-600 space-y-1">
                    <div>
                        <span class="text-slate-400">Invoice No:</span> 
                        <span class="font-mono font-bold text-slate-900 ml-1">{{ $invoice->invoice_number }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400">Status:</span>
                        @if($invoice->balance_amount <= 0)
                            <span class="ml-1 font-bold text-emerald-700 text-xs uppercase tracking-wide">Fully Paid</span>
                        @else
                            <span class="ml-1 font-bold text-slate-900 text-xs">Balance Due: ₹{{ number_format($invoice->balance_amount) }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Structured Metadata Strip -->
        <div class="flex flex-wrap items-center justify-between gap-4 py-3 px-4 bg-slate-50 border border-slate-200/90 text-xs my-6 rounded-md">
            <div class="flex-1 min-w-[120px]">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Invoice Date</span>
                <span class="font-medium text-slate-900">{{ $invoice->issue_date->format('d M Y') }}</span>
            </div>
            <div class="flex-1 min-w-[120px]">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Payment Due Date</span>
                <span class="font-medium text-slate-900">{{ $invoice->due_date->format('d M Y') }}</span>
            </div>
            <div class="flex-1 min-w-[120px]">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Shoot Booking Ref</span>
                <span class="font-mono font-medium text-slate-900">{{ $invoice->shoot?->shoot_number ?? '—' }}</span>
            </div>
            <div class="flex-1 min-w-[120px]">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Place of Supply</span>
                <span class="font-medium text-slate-900">{{ $settings->state }} (State Code: 29)</span>
            </div>
        </div>

        <!-- Party Details: Billed To & Shoot Assignment -->
        <div class="flex flex-col sm:flex-row justify-between gap-8 pb-6 border-b border-slate-200 text-xs">
            <!-- Client Info -->
            <div style="flex: 1 1 50%; min-width: 0;">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Billed To (Customer)</div>
                <div class="text-sm font-bold text-slate-900">{{ $invoice->customer?->name }}</div>
                <div class="text-slate-600 space-y-0.5 mt-1 leading-relaxed">
                    <p>{{ $invoice->customer?->address ?? 'Bengaluru, Karnataka' }}</p>
                    <p><span class="text-slate-400">Phone:</span> {{ $invoice->customer?->phone }}</p>
                    @if($invoice->customer?->email)
                        <p><span class="text-slate-400">Email:</span> {{ $invoice->customer->email }}</p>
                    @endif
                </div>
            </div>

            <!-- Shoot Assignment Reference -->
            <div style="flex: 1 1 50%; min-width: 0;">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Shoot &amp; Event Details</div>
                <div class="text-sm font-bold text-slate-900">{{ $invoice->shoot?->title ?? 'Photography Services' }}</div>
                <div class="text-slate-600 space-y-0.5 mt-1 leading-relaxed">
                    @if($invoice->shoot?->shoot_date)
                        <p><span class="text-slate-400">Date of Service:</span> {{ $invoice->shoot->shoot_date->format('d M Y') }}</p>
                    @endif
                    @if($invoice->shoot?->venue)
                        <p><span class="text-slate-400">Venue:</span> {{ $invoice->shoot->venue }}</p>
                    @endif
                    <p><span class="text-slate-400">Package:</span> {{ $invoice->shoot?->package?->name ?? $invoice->shoot?->event_type }}</p>
                </div>
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="py-6 border-b border-slate-200">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-y-2 border-slate-900 text-slate-800 uppercase text-[10px] font-bold">
                        <th class="py-2.5 px-3 text-center w-10">#</th>
                        <th class="py-2.5 px-3">Description of Services &amp; Deliverables</th>
                        <th class="py-2.5 px-3 text-center font-mono w-24">SAC Code</th>
                        <th class="py-2.5 px-3 text-center w-14">Qty</th>
                        <th class="py-2.5 px-3 text-right w-28">Rate (₹)</th>
                        <th class="py-2.5 px-3 text-right w-32">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    <tr>
                        <td class="py-3 px-3 text-center font-medium text-slate-400">1</td>
                        <td class="py-3 px-3">
                            <span class="font-bold text-slate-900 text-xs">{{ $invoice->shoot?->package?->name ?? ($invoice->shoot?->event_type . ' Photography Services') }}</span>
                            @if($invoice->shoot?->requirements)
                                <p class="text-slate-500 text-[11px] mt-1 leading-relaxed whitespace-pre-line">{{ $invoice->shoot->requirements }}</p>
                            @else
                                <p class="text-slate-500 text-[11px] mt-0.5 leading-relaxed">Professional photography, high-resolution coverage, and edited digital deliverables as per contracted package specifications.</p>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center font-mono text-slate-600">{{ $settings->hsn_sac_code }}</td>
                        <td class="py-3 px-3 text-center font-medium">1</td>
                        <td class="py-3 px-3 text-right font-medium text-slate-800">₹{{ number_format($invoice->subtotal, 2) }}</td>
                        <td class="py-3 px-3 text-right font-bold text-slate-900">₹{{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Verified Payments Received (Clean Sub-Table) -->
        @if($attachedPayments->isNotEmpty())
            <div class="py-5 border-b border-slate-200">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                    Payment Receipts Applied
                </div>
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 uppercase text-[9px] font-bold">
                            <th class="py-1.5 px-2">Receipt No</th>
                            <th class="py-1.5 px-2">Date</th>
                            <th class="py-1.5 px-2">Payment Mode</th>
                            <th class="py-1.5 px-2">Transaction Ref</th>
                            <th class="py-1.5 px-2 text-right">Amount Paid (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-600">
                        @foreach($attachedPayments as $p)
                            <tr>
                                <td class="py-1.5 px-2 font-mono font-medium text-slate-800">{{ $p->payment_number }}</td>
                                <td class="py-1.5 px-2">{{ $p->paid_at->format('d M Y') }}</td>
                                <td class="py-1.5 px-2 uppercase font-mono text-[10px]">{{ $p->payment_method }}</td>
                                <td class="py-1.5 px-2 font-mono text-[11px] text-slate-500">{{ $p->transaction_reference ?? '—' }}</td>
                                <td class="py-1.5 px-2 text-right font-semibold text-slate-900">₹{{ number_format($p->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Financial Summary Row: Amount in Words on Left, Totals on Right -->
        <div class="py-6 border-b border-slate-200">
            <div class="flex flex-col sm:flex-row justify-between items-start gap-8">
                <!-- Left: Amount in Words & Status -->
                <div style="flex: 1 1 55%; min-width: 0;" class="space-y-3">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Total Amount in Words</span>
                        <p class="text-xs font-semibold text-slate-900 italic bg-slate-50 p-3 rounded-md border border-slate-200/80 leading-relaxed">
                            {{ $invoice->amount_in_words }}
                        </p>
                    </div>
                    @if($invoice->balance_amount <= 0)
                        <div class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-2 rounded-md border border-emerald-200">
                            &#10003; Payment Status: Fully Paid (Zero Balance Due)
                        </div>
                    @else
                        <div class="text-xs font-bold text-slate-900 bg-slate-50 px-3 py-2 rounded-md border border-slate-200">
                            Outstanding Balance: ₹{{ number_format($invoice->balance_amount, 2) }}
                        </div>
                    @endif
                </div>

                <!-- Right: Calculation Breakdown Table -->
                <div style="flex: 0 0 320px; width: 320px;" class="space-y-1.5 text-xs">
                    <div class="flex justify-between py-1 text-slate-600">
                        <span>Subtotal:</span>
                        <span class="font-medium text-slate-800">₹{{ number_format($invoice->subtotal, 2) }}</span>
                    </div>
                    @if($invoice->discount_amount > 0)
                        <div class="flex justify-between py-1 text-slate-600">
                            <span>Discount:</span>
                            <span class="font-medium text-slate-800">-₹{{ number_format($invoice->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between py-1 text-slate-600">
                        <span>Taxable Value:</span>
                        <span class="font-medium text-slate-800">₹{{ number_format($taxableValue, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-1 text-slate-600">
                        <span>CGST ({{ $taxHalfRate }}%):</span>
                        <span class="font-medium text-slate-800">₹{{ number_format($cgst, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-1 text-slate-600">
                        <span>SGST ({{ $taxHalfRate }}%):</span>
                        <span class="font-medium text-slate-800">₹{{ number_format($sgst, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-t-2 border-slate-900 text-sm font-bold text-slate-900">
                        <span>Invoice Total:</span>
                        <span>₹{{ number_format($invoice->total_amount, 2) }}</span>
                    </div>
                    @if($invoice->paid_amount > 0)
                        <div class="flex justify-between py-1 border-t border-slate-200 text-slate-600">
                            <span>Total Paid:</span>
                            <span class="font-medium text-slate-800">(-) ₹{{ number_format($invoice->paid_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between py-2 border-t border-slate-300 font-bold text-slate-900">
                        <span class="text-xs uppercase tracking-wide">Net Balance Due:</span>
                        <span class="text-sm font-mono">₹{{ number_format($invoice->balance_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bank Details, UPI QR & Authorized Signatory Row -->
        <div class="py-6 border-b border-slate-200 flex flex-col sm:flex-row items-start justify-between gap-6 text-xs">
            <!-- Bank Details (approx 44% width) -->
            <div style="flex: 1 1 42%; min-width: 220px;" class="space-y-1.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Bank Transfer Details</span>
                <div class="text-slate-600 space-y-1">
                    <div class="flex"><span class="w-24 text-slate-400 shrink-0">Bank:</span><span class="font-semibold text-slate-800">{{ $settings->bank_name }}</span></div>
                    <div class="flex"><span class="w-24 text-slate-400 shrink-0">Account Name:</span><span class="text-slate-800">{{ $settings->bank_account_name }}</span></div>
                    <div class="flex"><span class="w-24 text-slate-400 shrink-0">Account No:</span><span class="font-mono font-bold text-slate-900">{{ $settings->bank_account_number }}</span></div>
                    <div class="flex"><span class="w-24 text-slate-400 shrink-0">IFSC Code:</span><span class="font-mono font-bold text-slate-900">{{ $settings->bank_ifsc }}</span></div>
                    @if($settings->bank_branch)
                        <div class="flex"><span class="w-24 text-slate-400 shrink-0">Branch:</span><span class="text-slate-700">{{ $settings->bank_branch }}</span></div>
                    @endif
                    <div class="flex pt-0.5"><span class="w-24 text-slate-400 shrink-0">UPI ID:</span><span class="font-mono font-medium text-slate-900">{{ $settings->upi_id }}</span></div>
                </div>
            </div>

            <!-- UPI QR Code (fixed 140px width) -->
            <div style="flex: 0 0 140px; width: 140px;" class="text-center flex flex-col items-center justify-center shrink-0">
                @if($settings->upi_id)
                    <img 
                        src="https://api.qrserver.com/v1/create-qr-code/?size=95x95&margin=0&data={{ urlencode($upiPayUrl) }}" 
                        alt="UPI QR Code" 
                        class="w-24 h-24 border border-slate-200 p-1 bg-white mx-auto shadow-2xs rounded"
                        onerror="this.parentElement.style.display='none'"
                    />
                    <span class="text-[9px] font-medium text-slate-500 block mt-1 tracking-tight">Scan to Pay via UPI</span>
                @endif
            </div>

            <!-- Authorized Signatory (approx 32% width) -->
            <div style="flex: 1 1 32%; min-width: 200px;" class="text-right flex flex-col justify-between shrink-0">
                <div class="text-xs font-semibold text-slate-800">For {{ $settings->studio_name }}</div>
                <div class="h-14"></div>
                <div class="border-t border-slate-300 pt-1.5 text-right w-48 ml-auto">
                    <div class="font-bold text-slate-900 text-xs">{{ $settings->authorized_signatory_name }}</div>
                    <div class="text-[11px] text-slate-500">{{ html_entity_decode($settings->authorized_signatory_title, ENT_QUOTES, 'UTF-8') }}</div>
                    <div class="text-[10px] text-slate-400 uppercase tracking-wider mt-0.5">Authorized Signatory</div>
                </div>
            </div>
        </div>

        <!-- Terms & Conditions (Full Width Footer) -->
        <div class="pt-6 text-xs text-slate-600 space-y-2">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Terms &amp; Conditions</div>
            <p class="whitespace-pre-line text-[11px] text-slate-500 leading-relaxed">{{ $invoice->terms ?: $settings->invoice_terms }}</p>
            @if($settings->invoice_notes)
                <p class="text-[11px] text-slate-700 italic mt-2">{{ $settings->invoice_notes }}</p>
            @endif
            <div class="text-[10px] text-slate-400 pt-3 border-t border-slate-100 flex items-center justify-between">
                <span>This is a formal GST Tax Invoice issued under Section 31 of CGST Act, 2017.</span>
                <span>Page 1 of 1</span>
            </div>
        </div>
    </div>
</div>

<!-- Print Stylesheet -->
<style>
@media print {
    @page {
        size: A4 portrait;
        margin: 12mm 15mm 15mm 15mm;
    }
    body {
        background-color: #ffffff !important;
        color: #000000 !important;
    }
    aside, header, footer, #toast-container, .print\:hidden {
        display: none !important;
    }
    .lg\:pl-64 {
        padding-left: 0 !important;
    }
    main {
        padding: 0 !important;
        margin: 0 !important;
    }
    #printable-invoice {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
    }
}
</style>
@endsection


