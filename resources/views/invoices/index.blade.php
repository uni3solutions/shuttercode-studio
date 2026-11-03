@extends('layouts.app', [
    'title' => 'Invoices & Billing',
    'breadcrumbs' => ['Home' => '/', 'Billing' => '#', 'Invoices' => route('invoices.index')]
])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Tax Invoices & Billing</h2>
            <p class="text-xs text-slate-500 mt-0.5">Generate GST compliant customer invoices and print formal payment vouchers</p>
        </div>
        <div class="flex items-center gap-2.5">
            <x-button variant="primary" size="sm" icon="plus" href="{{ route('invoices.create') }}">
                <span>Create Invoice</span>
            </x-button>
        </div>
    </div>

    <!-- Invoices Table -->
    <x-card :noPadding="true">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4 first:pl-5">Invoice No</th>
                        <th class="py-3 px-4">Client Name</th>
                        <th class="py-3 px-4">Shoot Booking</th>
                        <th class="py-3 px-4">Total Amount (Inc. GST)</th>
                        <th class="py-3 px-4">Paid</th>
                        <th class="py-3 px-4">Balance</th>
                        <th class="py-3 px-4">Issue Date</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right pr-5">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($invoices as $inv)
                        @php
                            $invVariants = [
                                'paid' => 'success',
                                'partially_paid' => 'amber',
                                'sent' => 'info',
                                'draft' => 'neutral',
                                'overdue' => 'danger',
                            ];
                            $v = $invVariants[$inv->status] ?? 'neutral';
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 first:pl-5 font-mono font-bold text-indigo-600">
                                <span>{{ $inv->invoice_number }}</span>
                                @if($inv->telecaller)
                                    <span class="block text-[10px] font-sans font-normal text-slate-400 mt-0.5">{{ $inv->telecaller->name }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-900">
                                {{ $inv->customer?->name }}
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('shoots.show', $inv->shoot_id) }}" class="font-medium text-slate-800 hover:text-[#4F46E5] hover:underline">
                                    {{ $inv->shoot?->title }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900 text-sm">
                                ₹{{ number_format($inv->total_amount) }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-emerald-600">
                                ₹{{ number_format($inv->paid_amount) }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold {{ $inv->balance_amount > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                                ₹{{ number_format($inv->balance_amount) }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">
                                {{ $inv->issue_date->format('d M Y') }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($inv->balance_amount <= 0)
                                    <div class="inline-flex flex-col items-start">
                                        <x-badge variant="success" size="xs" :dot="true">Fully Paid</x-badge>
                                        <span class="text-[10px] font-medium text-emerald-600 mt-0.5 whitespace-nowrap">Zero Balance</span>
                                    </div>
                                @elseif($inv->paid_amount > 0)
                                    <div class="inline-flex flex-col items-start">
                                        <x-badge variant="warning" size="xs" :dot="true">Partial</x-badge>
                                        <span class="text-[11px] font-semibold text-amber-700 mt-0.5 whitespace-nowrap">
                                            Due: ₹{{ number_format($inv->balance_amount) }}
                                        </span>
                                    </div>
                                @else
                                    <x-badge :variant="$v" size="xs" :dot="true">
                                        {{ ucwords(str_replace('_', ' ', $inv->status)) }}
                                    </x-badge>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right pr-5 whitespace-nowrap">
                                <div class="inline-flex items-center gap-1 justify-end">
                                    <a href="{{ route('invoices.show', $inv) }}" title="View Invoice" class="w-7 h-7 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-[#4F46E5] hover:bg-indigo-50/80 transition-colors">
                                        <x-icon name="eye" class="w-4 h-4" />
                                    </a>
                                    <a href="{{ route('invoices.show', $inv) }}" target="_blank" title="Print Invoice" class="w-7 h-7 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors">
                                        <x-icon name="printer" class="w-3.5 h-3.5" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center text-slate-400">
                                <x-icon name="file-text" class="w-8 h-8 mx-auto text-slate-300 mb-2" />
                                <p class="text-sm font-medium text-slate-600">No invoices generated yet</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">
                {{ $invoices->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
