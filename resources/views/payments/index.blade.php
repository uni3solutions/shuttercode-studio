@extends('layouts.app', [
    'title' => 'Payments & Financial Ledger',
    'breadcrumbs' => ['Home' => '/', 'Finance' => '#', 'Payments' => route('payments.index')]
])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Payments & Financial Transactions</h2>
            <p class="text-xs text-slate-500 mt-0.5">Verified customer advances, installment receipts, and balance calculations</p>
        </div>
        <div class="flex items-center gap-2.5">
            <x-button variant="primary" size="sm" icon="plus" onclick="document.getElementById('modal-new-payment').classList.remove('hidden')">
                <span>Record Payment</span>
            </x-button>
        </div>
    </div>

    <!-- Financial Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-stat-card 
            title="Total Revenue Collected" 
            :value="'₹' . number_format($totalCollected)" 
            icon="banknote" 
            iconColor="emerald" 
            subtitle="Verified transactions"
        />
        <x-stat-card 
            title="Outstanding Customer Balances" 
            :value="'₹' . number_format($totalOutstanding)" 
            icon="alert-circle" 
            iconColor="amber" 
            subtitle="Across scheduled shoots"
        />
        <x-stat-card 
            title="Total Receipts Issued" 
            :value="$payments->total() . ' Transactions'" 
            icon="receipt" 
            iconColor="indigo" 
        />
    </div>

    <!-- Payments Ledger Table -->
    <x-card :noPadding="true">
        <form method="GET" action="{{ route('payments.index') }}" class="p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50 border-b border-slate-100">
            <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <x-icon name="search" class="w-4 h-4" />
                </div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search by receipt no, client name, or UTR..." 
                    class="w-full pl-9 pr-3.5 py-1.5 text-xs bg-white rounded-lg border border-slate-200 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                />
            </div>
            <div class="flex items-center gap-2">
                <select name="method" class="text-xs bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-600">
                    <option value="">All Payment Methods</option>
                    <option value="upi" @selected(request('method') === 'upi')>UPI</option>
                    <option value="bank_transfer" @selected(request('method') === 'bank_transfer')>Bank Transfer</option>
                    <option value="cash" @selected(request('method') === 'cash')>Cash</option>
                    <option value="card" @selected(request('method') === 'card')>Card</option>
                </select>
                <x-button variant="primary" size="sm" type="submit" icon="filter">
                    Filter
                </x-button>
                @if(request('search') || request('method'))
                    <x-button variant="secondary" size="sm" href="{{ route('payments.index') }}">
                        Reset
                    </x-button>
                @endif
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4 first:pl-5">Receipt No</th>
                        <th class="py-3 px-4">Invoice</th>
                        <th class="py-3 px-4">Client Name</th>
                        <th class="py-3 px-4">Shoot Title</th>
                        <th class="py-3 px-4">Amount</th>
                        <th class="py-3 px-4">Method</th>
                        <th class="py-3 px-4">Payment Type</th>
                        <th class="py-3 px-4">Transaction UTR</th>
                        <th class="py-3 px-4">Paid At</th>
                        <th class="py-3 px-4 text-right pr-5">Payment Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($payments as $payment)
                        @php
                            $inv = $payment->invoice ?? $payment->shoot?->invoices->first();
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4 first:pl-5 font-mono font-bold text-indigo-600">
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
                                    <span class="text-slate-400 font-mono text-[11px]">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-900">
                                {{ $payment->customer?->name }}
                            </td>
                            <td class="py-3 px-4">
                                <a href="{{ route('shoots.show', $payment->shoot_id) }}" class="font-medium text-slate-800 hover:text-[#4F46E5] hover:underline">
                                    {{ $payment->shoot?->title }}
                                </a>
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-900 text-sm">
                                ₹{{ number_format($payment->amount) }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-slate-100 text-slate-700">
                                    {{ $payment->payment_method }}
                                </span>
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
                                @elseif(($payment->shoot?->balance_amount ?? 0) <= 0)
                                    <div class="inline-flex flex-col items-end">
                                        <x-badge variant="success" size="xs" :dot="true">Fully Paid</x-badge>
                                        <span class="text-[10px] font-medium text-emerald-600 mt-0.5 whitespace-nowrap">Zero Balance</span>
                                    </div>
                                @else
                                    <div class="inline-flex flex-col items-end">
                                        <x-badge variant="warning" size="xs" :dot="true">Partial</x-badge>
                                        <span class="text-[11px] font-semibold text-amber-700 mt-0.5 whitespace-nowrap">
                                            Due: ₹{{ number_format($payment->shoot->balance_amount) }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-12 text-center text-slate-400">
                                <x-icon name="receipt" class="w-8 h-8 mx-auto text-slate-300 mb-2" />
                                <p class="text-sm font-medium text-slate-600">No payment transactions recorded</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">
                {{ $payments->links() }}
            </div>
        @endif
    </x-card>
</div>

<!-- Modal: New Payment -->
<x-modal id="modal-new-payment" title="Record New Customer Payment" subtitle="Verify and record client payments">
    <form action="{{ route('payments.store') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <x-select label="Select Shoot Booking" name="shoot_id" required>
                <option value="">-- Choose Shoot with Due Balance --</option>
                @foreach($shoots as $sh)
                    <option value="{{ $sh->id }}">
                        {{ $sh->shoot_number }} — {{ $sh->title }} (Due: ₹{{ number_format($sh->balance_amount) }})
                    </option>
                @endforeach
            </x-select>
        </div>

        <div>
            <x-input 
                label="Payment Amount (₹)" 
                name="amount" 
                type="number" 
                step="0.01" 
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
            <x-button variant="secondary" size="sm" onclick="document.getElementById('modal-new-payment').classList.add('hidden')">
                Cancel
            </x-button>
            <x-button variant="primary" size="sm" type="submit">
                Confirm Payment
            </x-button>
        </div>
    </form>
</x-modal>
@endsection
