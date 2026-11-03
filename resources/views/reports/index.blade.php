@extends('layouts.app', [
    'title' => 'Studio Analytics & Reports',
    'breadcrumbs' => ['Home' => '/', 'Management' => '#', 'Reports' => route('reports.index')]
])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Studio Performance & Financial Analytics</h2>
            <p class="text-xs text-slate-500 mt-0.5">Revenue summaries, booking distribution, crew efficiency, and lead conversion</p>
        </div>
    </div>

    <!-- Metrics Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card 
            title="Total Revenue Realized" 
            :value="'₹' . number_format($totalRevenue)" 
            icon="banknote" 
            iconColor="emerald" 
            trend="+24% YoY"
            :trendUp="true"
        />
        <x-stat-card 
            title="Total Shoots Booked" 
            :value="$totalShootsCount . ' Bookings'" 
            icon="camera" 
            iconColor="indigo" 
            trend="Active calendar"
            :trendUp="true"
        />
        <x-stat-card 
            title="Uncollected Balances" 
            :value="'₹' . number_format($totalBalance)" 
            icon="alert-circle" 
            iconColor="amber" 
            trend="Due before delivery"
            :trendUp="false"
        />
        <x-stat-card 
            title="Lead Conversion Rate" 
            :value="$conversionRate . '%'" 
            icon="trending-up" 
            iconColor="purple" 
            subtitle="{{ $convertedLeads }} of {{ $totalLeads }} converted"
        />
    </div>

    <!-- Charts & Breakdown Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Event Category Distribution -->
        <x-card title="Revenue Breakdown by Event Category" subtitle="Distribution of bookings across wedding, pre-wedding, and events">
            <div class="space-y-4">
                @foreach($shootsByCategory as $item)
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-bold text-slate-800">{{ $item->event_type }}</span>
                            <span class="text-slate-600 font-semibold">{{ $item->count }} Shoots &bull; ₹{{ number_format($item->total) }}</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full bg-indigo-600 rounded-full" style="width: {{ min(100, ($item->total / max(1, $totalRevenue)) * 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>

        <!-- Photographer Workload & Payout Table -->
        <x-card title="Creative Crew Workload" subtitle="Assigned shoot count per team artist">
            <div class="space-y-3">
                @foreach($photographers as $ph)
                    <div class="flex items-center justify-between p-3 rounded-lg border border-slate-100 hover:bg-slate-50 transition-colors text-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-700 font-bold flex items-center justify-center text-xs">
                                {{ substr($ph->name, 0, 1) }}
                            </div>
                            <div>
                                <span class="font-bold text-slate-900">{{ $ph->name }}</span>
                                <p class="text-slate-500 text-[11px]">{{ $ph->specialization ?? 'Photographer' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-indigo-600">{{ $ph->assignments_count }} Shoots</span>
                            <span class="text-[10px] text-slate-400 block">Allocated</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>

        <!-- Monthly Verified Collections -->
        <x-card title="Monthly Verified Revenue Trend" subtitle="Cash-flow receipts over recent cycles">
            @if($monthlyRevenue->count() > 0)
                <div class="space-y-3">
                    @foreach($monthlyRevenue as $rev)
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50/80 border border-slate-100 text-xs">
                            <span class="font-semibold text-slate-800">{{ $rev->month_name }}</span>
                            <span class="font-mono font-bold text-emerald-600">₹{{ number_format($rev->total) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-400 italic">No historical revenue data recorded yet.</p>
            @endif
        </x-card>

        <!-- Studio Activity Audit Log -->
        <x-card title="Recent Operational Activity" subtitle="Live stream of booking, payment, and crew assignments">
            <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
                @forelse($activityLogs as $log)
                    <div class="flex items-start gap-2.5 text-xs pb-2 border-b border-slate-100 last:border-0 last:pb-0">
                        <div class="w-6 h-6 rounded-md bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 mt-0.5">
                            <x-icon name="activity" class="w-3.5 h-3.5" />
                        </div>
                        <div class="flex-1">
                            <p class="text-slate-800 font-medium leading-snug">{{ $log->description }}</p>
                            <span class="text-[10px] text-slate-400">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 italic">No activity recorded yet.</p>
                @endforelse
            </div>
        </x-card>
    </div>
</div>
@endsection
