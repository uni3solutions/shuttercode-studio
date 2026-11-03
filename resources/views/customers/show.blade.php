@extends('layouts.app', [
    'title' => $customer->name . ' — Profile',
    'breadcrumbs' => ['Home' => '/', 'Customers' => route('customers.index'), $customer->name => '#']
])

@section('content')
<div class="space-y-6">
    <!-- Profile Header Card -->
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 font-bold text-xl flex items-center justify-center shrink-0">
                {{ substr($customer->name, 0, 1) }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">{{ $customer->name }}</h2>
                    <x-badge variant="info" size="xs">Verified Client</x-badge>
                </div>
                <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-slate-500 mt-1">
                    <span class="flex items-center gap-1">
                        <x-icon name="phone" class="w-3.5 h-3.5 text-slate-400" />
                        {{ $customer->phone }}
                    </span>
                    @if($customer->email)
                        <span class="flex items-center gap-1">
                            <x-icon name="mail" class="w-3.5 h-3.5 text-slate-400" />
                            {{ $customer->email }}
                        </span>
                    @endif
                    <span class="flex items-center gap-1">
                        <x-icon name="map-pin" class="w-3.5 h-3.5 text-slate-400" />
                        {{ $customer->city ?? 'Bengaluru' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            @php
                $cleanPhone = preg_replace('/[^0-9]/', '', $customer->phone);
                if (strlen($cleanPhone) === 10) $cleanPhone = '91' . $cleanPhone;
            @endphp
            <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors">
                <x-icon name="message-circle" class="w-4 h-4 text-emerald-600" />
                <span>WhatsApp Client</span>
            </a>
            <x-button variant="secondary" size="sm" icon="pencil" href="{{ route('customers.edit', $customer) }}">
                Edit Details
            </x-button>
            <x-button variant="primary" size="sm" icon="plus" href="{{ route('shoots.create', ['customer_id' => $customer->id]) }}">
                New Shoot
            </x-button>
        </div>
    </div>

    <!-- Stats Overview for Customer -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-stat-card 
            title="Total Bookings" 
            :value="$customer->shoots->count() . ' Shoots'" 
            icon="camera" 
            iconColor="indigo" 
        />
        <x-stat-card 
            title="Total Spent" 
            :value="'₹' . number_format($customer->total_spent)" 
            icon="credit-card" 
            iconColor="emerald" 
        />
        <x-stat-card 
            title="Invoices Generated" 
            :value="$customer->invoices->count() . ' Invoices'" 
            icon="file-text" 
            iconColor="purple" 
        />
    </div>

    <!-- Shoots History Table -->
    <x-card :noPadding="true">
        <x-slot:title>
            <span>Shoots & Events Booked</span>
        </x-slot:title>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4 first:pl-5">Shoot ID</th>
                        <th class="py-3 px-4">Event & Title</th>
                        <th class="py-3 px-4">Package</th>
                        <th class="py-3 px-4">Date & Venue</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Balance</th>
                        <th class="py-3 px-4 text-right pr-5">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($customer->shoots as $shoot)
                        @php
                            $statusVariants = [
                                'scheduled' => 'neutral',
                                'crew_assigned' => 'sky',
                                'in_progress' => 'info',
                                'raw_uploaded' => 'purple',
                                'editing' => 'amber',
                                'client_review' => 'warning',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                            ];
                            $variant = $statusVariants[$shoot->status] ?? 'neutral';
                            $statusLabel = ucwords(str_replace('_', ' ', $shoot->status));
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4 first:pl-5 font-mono font-bold text-indigo-600">
                                {{ $shoot->shoot_number }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-semibold text-slate-900">{{ $shoot->title }}</span>
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-700">
                                {{ $shoot->package?->name ?? 'Custom Package' }}
                            </td>
                            <td class="py-3 px-4">
                                <div>{{ $shoot->shoot_date->format('d M Y') }}</div>
                                <div class="text-[11px] text-slate-400 truncate max-w-xs">{{ $shoot->venue }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <x-badge :variant="$variant" :dot="true">
                                    {{ $statusLabel }}
                                </x-badge>
                            </td>
                            <td class="py-3 px-4">
                                @if($shoot->balance_amount > 0)
                                    <span class="font-semibold text-amber-600">Due: ₹{{ number_format($shoot->balance_amount) }}</span>
                                @else
                                    <span class="font-semibold text-emerald-600">Cleared</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right pr-5 whitespace-nowrap">
                                <div class="inline-flex items-center gap-1 justify-end">
                                    @php
                                        $shootInv = $shoot->invoices->first();
                                    @endphp
                                    @if($shootInv)
                                        <a href="{{ route('invoices.show', $shootInv) }}" title="View Tax Invoice {{ $shootInv->invoice_number }}" class="w-7 h-7 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-[#4F46E5] hover:bg-indigo-50/80 transition-colors">
                                            <x-icon name="file-text" class="w-4 h-4" />
                                        </a>
                                    @endif
                                    <a href="{{ route('shoots.show', $shoot) }}" title="View Shoot Details" class="w-7 h-7 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-[#4F46E5] hover:bg-indigo-50/80 transition-colors">
                                        <x-icon name="eye" class="w-4 h-4" />
                                    </a>
                                    <a href="{{ route('shoots.edit', $shoot) }}" title="Edit Shoot" class="w-7 h-7 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors">
                                        <x-icon name="pencil" class="w-3.5 h-3.5" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                No shoots booked yet for this customer.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <!-- Payment Transaction History -->
    <x-card :noPadding="true">
        <x-slot:title>
            <span>Payment History</span>
        </x-slot:title>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4 first:pl-5">Receipt No</th>
                        <th class="py-3 px-4">Invoice</th>
                        <th class="py-3 px-4">Shoot</th>
                        <th class="py-3 px-4">Amount</th>
                        <th class="py-3 px-4">Method</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Reference</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right pr-5">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($customer->payments as $payment)
                        @php
                            $inv = $payment->invoice ?? $payment->shoot?->invoices->first();
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
                                    <span class="text-slate-400 font-mono text-[11px]">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-900">
                                <a href="{{ route('shoots.show', $payment->shoot_id) }}" class="hover:text-[#4F46E5] hover:underline">
                                    {{ $payment->shoot?->title ?? '—' }}
                                </a>
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-900">
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
                            <td class="py-3 px-4">
                                <x-badge variant="success" size="xs">Verified</x-badge>
                            </td>
                            <td class="py-3 px-4 text-right pr-5 whitespace-nowrap">
                                @if($inv)
                                    <div class="inline-flex items-center gap-1 justify-end">
                                        <a href="{{ route('invoices.show', $inv) }}" 
                                           title="View Tax Invoice {{ $inv->invoice_number }}" 
                                           class="w-7 h-7 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-[#4F46E5] hover:bg-indigo-50/80 transition-colors">
                                            <x-icon name="file-text" class="w-4 h-4" />
                                        </a>
                                        <a href="{{ route('invoices.show', $inv) }}" 
                                           target="_blank" 
                                           title="Print Tax Invoice" 
                                           class="w-7 h-7 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors">
                                            <x-icon name="printer" class="w-3.5 h-3.5" />
                                        </a>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-8 text-center text-slate-400">
                                No payments recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
@endsection
