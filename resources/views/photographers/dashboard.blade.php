@extends('layouts.app', [
    'title' => 'Photographer Dashboard',
    'breadcrumbs' => ['Home' => '/my-dashboard', 'Dashboard' => '#']
])

@section('content')
<div class="space-y-6">
    <!-- Artist Profile Banner -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-indigo-50 border border-indigo-100 text-[#4F46E5] font-bold text-2xl flex items-center justify-center shrink-0">
                <x-icon name="camera" class="w-8 h-8" />
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Welcome, {{ $user->name }}</h2>
                    <x-badge variant="info" size="xs">{{ $user->specialization ?? 'Creative Artist' }}</x-badge>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Your personal photographer command center &bull; Lifetime earnings, active shoots & payout status
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <x-button variant="secondary" size="sm" icon="credit-card" href="{{ route('photographers.payments') }}">
                My Payouts Ledger
            </x-button>
            <x-button variant="primary" size="sm" icon="camera" href="{{ route('photographers.portal') }}">
                View All Shoots
            </x-button>
        </div>
    </div>

    <!-- 4 Core Lifetime & Operational Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Lifetime Income -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Working Income</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <x-icon name="banknote" class="w-4 h-4" />
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold text-slate-900 tracking-tight">₹{{ number_format($totalLifetimeIncome) }}</span>
                <p class="text-xs text-emerald-600 font-medium mt-1 flex items-center gap-1">
                    <x-icon name="check-circle" class="w-3.5 h-3.5" />
                    <span>Disbursed to date</span>
                </p>
            </div>
        </div>

        <!-- Pending Payout -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Balance Left Due</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <x-icon name="clock" class="w-4 h-4" />
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold {{ $totalPendingBalance > 0 ? 'text-amber-600' : 'text-slate-900' }} tracking-tight">
                    ₹{{ number_format($totalPendingBalance) }}
                </span>
                <p class="text-xs text-slate-500 font-medium mt-1">
                    {{ $totalPendingBalance > 0 ? 'Pending payout from studio' : 'All accounts settled' }}
                </p>
            </div>
        </div>

        <!-- Pending Shoots -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pending Shoots</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <x-icon name="calendar" class="w-4 h-4" />
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold text-slate-900 tracking-tight">{{ $pendingShootsCount }}</span>
                <p class="text-xs text-indigo-600 font-medium mt-1">
                    Active & scheduled bookings
                </p>
            </div>
        </div>

        <!-- Completed Shoots -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Completed Shoots</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                    <x-icon name="check-check" class="w-4 h-4" />
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold text-slate-900 tracking-tight">{{ $completedShootsCount }}</span>
                <p class="text-xs text-slate-500 font-medium mt-1">
                    Successfully delivered
                </p>
            </div>
        </div>
    </div>

    <!-- 2 Attention Action Alerts -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Alert 1: Data submission pending -->
        <div class="p-4 rounded-xl border {{ $dataPendingCount > 0 ? 'bg-amber-50/60 border-amber-200' : 'bg-slate-50 border-slate-200/80' }} flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl {{ $dataPendingCount > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-200 text-slate-600' }} flex items-center justify-center shrink-0">
                    <x-icon name="upload-cloud" class="w-5 h-5" />
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-900">RAW Data Submission Pending</h4>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ $dataPendingCount > 0 ? "{$dataPendingCount} shoot(s) shot but RAW Drive folder link is pending upload." : 'All completed shoots have RAW links uploaded.' }}
                    </p>
                </div>
            </div>
            @if($dataPendingCount > 0)
                <a href="{{ route('photographers.portal', ['category' => 'data_pending']) }}" class="px-3 py-1.5 text-xs font-semibold bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors shrink-0">
                    Upload Link &rarr;
                </a>
            @endif
        </div>

        <!-- Alert 2: Data submitted, payment due -->
        <div class="p-4 rounded-xl border {{ $dataSubmittedPaymentDueCount > 0 ? 'bg-indigo-50/60 border-indigo-200' : 'bg-slate-50 border-slate-200/80' }} flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl {{ $dataSubmittedPaymentDueCount > 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-200 text-slate-600' }} flex items-center justify-center shrink-0">
                    <x-icon name="credit-card" class="w-5 h-5" />
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-900">Data Submitted &bull; Payout Rest</h4>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ $dataSubmittedPaymentDueCount > 0 ? "{$dataSubmittedPaymentDueCount} shoot(s) delivered with remaining agency balance due." : 'No pending payment on submitted shoots.' }}
                    </p>
                </div>
            </div>
            @if($dataSubmittedPaymentDueCount > 0)
                <a href="{{ route('photographers.portal', ['category' => 'payment_due']) }}" class="px-3 py-1.5 text-xs font-semibold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shrink-0">
                    View Shoots &rarr;
                </a>
            @endif
        </div>
    </div>

    <!-- Active Shoots & Recent Disbursements Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Assigned Shoots (2 cols) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Upcoming & Recent Assignments</h3>
                <a href="{{ route('photographers.portal') }}" class="text-xs font-semibold text-[#4F46E5] hover:underline">
                    View All Shoots &rarr;
                </a>
            </div>

            <div class="space-y-3">
                @forelse($recentShoots as $assignment)
                    @php $shoot = $assignment->shoot; @endphp
                    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-indigo-200 transition-colors">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">
                                    {{ $shoot->shoot_number }}
                                </span>
                                <h4 class="text-sm font-bold text-slate-900">{{ $shoot->title }}</h4>
                                <x-badge variant="info" size="xs">{{ $shoot->event_type }}</x-badge>
                            </div>
                            <div class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-x-3 gap-y-0.5">
                                <span>Customer: <strong class="text-slate-700">{{ $shoot->customer?->name }}</strong></span>
                                <span>&bull;</span>
                                <span>{{ $shoot->shoot_date?->format('d M Y') }}</span>
                                <span>&bull;</span>
                                <span class="truncate max-w-[200px]">{{ $shoot->venue }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-3 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                            <div class="text-right">
                                <span class="text-[10px] text-slate-400 block uppercase font-medium">Agreed Fee</span>
                                <span class="text-xs font-bold text-slate-900">₹{{ number_format($assignment->payout_agreed) }}</span>
                                <span class="text-[10px] {{ $assignment->balance_due > 0 ? 'text-amber-600 font-semibold' : 'text-emerald-600' }} block">
                                    {{ $assignment->balance_due > 0 ? '₹' . number_format($assignment->balance_due) . ' due' : 'Paid' }}
                                </span>
                            </div>

                            <a href="{{ route('photographers.portal') }}" class="p-2 rounded-lg bg-slate-50 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 transition-colors">
                                <x-icon name="arrow-right" class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="bg-white p-8 rounded-xl border border-slate-200/80 text-center text-slate-400 text-xs">
                        No assigned shoots yet.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Payment Disbursements (1 col) -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Latest Disbursements</h3>
                <a href="{{ route('photographers.payments') }}" class="text-xs font-semibold text-[#4F46E5] hover:underline">
                    All Payouts &rarr;
                </a>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 divide-y divide-slate-100 shadow-2xs overflow-hidden">
                @forelse($recentPayments as $payment)
                    <div class="p-3.5 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span class="font-mono text-[11px] font-bold text-slate-800">{{ $payment->payment_number }}</span>
                                <span class="px-1.5 py-0.2 bg-emerald-100 text-emerald-800 rounded text-[9px] font-bold uppercase">Paid</span>
                            </div>
                            <p class="text-xs text-slate-600 truncate mt-0.5">{{ $payment->shoot?->title ?? 'Shoot Payment' }}</p>
                            <span class="text-[10px] text-slate-400 block">{{ $payment->paid_at?->format('d M Y') ?? 'Recent' }} via {{ strtoupper($payment->payment_method) }}</span>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-sm font-bold text-emerald-600">+₹{{ number_format($payment->amount) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-400 text-xs">
                        No payments recorded yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
