@extends('layouts.app', [
    'title' => 'My Payouts & Invoices',
    'breadcrumbs' => ['Home' => '/my-dashboard', 'Payouts & Invoices' => '#']
])

@section('content')
<div class="space-y-6">
    <!-- Payout Overview Banner -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-slate-100">
            <div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Photographer Earnings &amp; Invoices</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Your complete shoot payment history, remuneration vouchers, and official printable payout invoices
                </p>
            </div>

            <div class="flex items-center gap-2">
                <x-button variant="secondary" size="sm" icon="camera" href="{{ route('photographers.portal') }}">
                    My Assigned Shoots
                </x-button>
            </div>
        </div>

        <!-- 3 Financial Summary Cards -->
        <div class="pt-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Total Agreed Contract Fees</span>
                <span class="text-2xl font-bold text-slate-900 mt-1 block">₹{{ number_format($totalAgreed) }}</span>
                <span class="text-[10px] text-slate-500">Across {{ $assignments->count() }} assigned bookings</span>
            </div>

            <div class="p-4 bg-emerald-50/70 rounded-xl border border-emerald-100">
                <span class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wider block">Total Income Received</span>
                <span class="text-2xl font-bold text-emerald-700 mt-1 block">₹{{ number_format($totalLifetimeIncome) }}</span>
                <span class="text-[10px] text-emerald-600 font-medium">Disbursed to your account to date</span>
            </div>

            <div class="p-4 bg-amber-50/70 rounded-xl border border-amber-100">
                <span class="text-[11px] font-semibold text-amber-700 uppercase tracking-wider block">Remaining Balance Due</span>
                <span class="text-2xl font-bold {{ $totalPendingBalance > 0 ? 'text-amber-700' : 'text-slate-700' }} mt-1 block">
                    ₹{{ number_format($totalPendingBalance) }}
                </span>
                <span class="text-[10px] text-amber-600 font-medium">
                    {{ $totalPendingBalance > 0 ? 'Pending payout from studio' : 'All accounts settled' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Unified Shoot Payment & Invoice History Table -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Shoot Payment History &amp; Invoices</h3>
                <p class="text-xs text-slate-400 mt-0.5">Click "View Invoice" to view and print official payout invoices with full remittance breakdown</p>
            </div>
            <span class="text-xs text-slate-400 font-semibold">{{ $assignments->count() }} Shoots</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="p-3.5">Shoot &amp; Customer</th>
                            <th class="p-3.5">Shoot Date</th>
                            <th class="p-3.5">Agreed Fee</th>
                            <th class="p-3.5">Agency Paid</th>
                            <th class="p-3.5">Balance Rest</th>
                            <th class="p-3.5">Settlement Status</th>
                            <th class="p-3.5">RAW Data</th>
                            <th class="p-3.5 text-right">Official Invoice</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($assignments as $assignment)
                            @php 
                                $shoot = $assignment->shoot; 
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <!-- Shoot & Customer -->
                                <td class="p-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <span class="font-mono text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded font-bold text-[11px] border border-indigo-100">
                                            {{ $shoot->shoot_number }}
                                        </span>
                                        <div>
                                            <p class="font-bold text-slate-900 text-xs">{{ $shoot->title }}</p>
                                            <p class="text-[11px] text-slate-500">Customer: <strong class="text-slate-700">{{ $shoot->customer?->name }}</strong></p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Shoot Date -->
                                <td class="p-3.5 text-slate-600 whitespace-nowrap">
                                    {{ $shoot->shoot_date?->format('d M Y') ?? '—' }}
                                </td>

                                <!-- Agreed Company Fee -->
                                <td class="p-3.5 font-bold text-slate-900 whitespace-nowrap text-xs">
                                    ₹{{ number_format($assignment->payout_agreed) }}
                                </td>

                                <!-- Agency Paid -->
                                <td class="p-3.5 whitespace-nowrap text-xs">
                                    <span class="font-bold text-emerald-600 block">₹{{ number_format($assignment->amount_paid) }}</span>
                                    @if($assignment->latest_payment)
                                        <span class="font-mono text-[10px] text-slate-400 block mt-0.5">
                                            {{ $assignment->latest_payment->payment_number }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Balance Rest -->
                                <td class="p-3.5 font-bold {{ $assignment->balance_due > 0 ? 'text-amber-600' : 'text-slate-400' }} whitespace-nowrap text-xs">
                                    ₹{{ number_format($assignment->balance_due) }}
                                </td>

                                <!-- Settlement Status -->
                                <td class="p-3.5 whitespace-nowrap">
                                    @if($assignment->balance_due <= 0 && $assignment->payout_agreed > 0)
                                        <x-badge variant="success" size="xs">Settled in Full</x-badge>
                                    @elseif($assignment->amount_paid > 0)
                                        <x-badge variant="warning" size="xs">Partially Paid</x-badge>
                                    @else
                                        <x-badge variant="neutral" size="xs">Payment Pending</x-badge>
                                    @endif
                                </td>

                                <!-- RAW File Status -->
                                <td class="p-3.5 whitespace-nowrap">
                                    @if($shoot->delivery?->raw_drive_link)
                                        <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded text-[10px] border border-emerald-200">
                                            <x-icon name="check" class="w-3 h-3 text-emerald-600" />
                                            RAW Ingested
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-amber-700 font-medium bg-amber-50 px-2 py-0.5 rounded text-[10px] border border-amber-200">
                                            <x-icon name="clock" class="w-3 h-3 text-amber-500" />
                                            Pending Upload
                                        </span>
                                    @endif
                                </td>

                                <!-- Official Invoice Button -->
                                <td class="p-3.5 text-right whitespace-nowrap">
                                    <x-button 
                                        variant="primary" 
                                        size="xs" 
                                        icon="file-text"
                                        href="{{ route('photographers.shoots.invoice', $shoot) }}"
                                    >
                                        View Invoice
                                    </x-button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-10 text-center text-slate-400">
                                    <x-icon name="credit-card" class="w-8 h-8 mx-auto text-slate-300 mb-2" />
                                    <p class="font-semibold text-slate-700">No Shoot Assignments Found</p>
                                    <p class="text-xs text-slate-400 mt-1">Once you are assigned to shoot bookings, your payment statements and invoices will appear here.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
