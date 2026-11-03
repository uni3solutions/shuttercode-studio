@extends('layouts.app', [
    'title' => 'Shoots & Bookings Calendar',
    'breadcrumbs' => ['Home' => '/', 'Operations' => '#', 'Shoots' => route('shoots.index')]
])

@section('content')
<div class="space-y-5">
    <!-- Header Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Shoot Bookings & Schedule</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage event timelines, crew allocation, and post-production workflows</p>
        </div>
        <div class="flex items-center gap-2.5">
            <x-button variant="primary" size="sm" icon="plus" href="{{ route('shoots.create') }}">
                <span>New Shoot Booking</span>
            </x-button>
        </div>
    </div>

    <!-- Status Tabs with Live Counters -->
    @php
        $currentStatus = request('status', '');
        $statusTabs = [
            '' => ['label' => 'All Shoots', 'count' => $statusCounts['all'] ?? 0],
            'scheduled' => ['label' => 'Scheduled', 'count' => $statusCounts['scheduled'] ?? 0],
            'crew_assigned' => ['label' => 'Crew Assigned', 'count' => $statusCounts['crew_assigned'] ?? 0],
            'in_progress' => ['label' => 'In Progress', 'count' => $statusCounts['in_progress'] ?? 0],
            'raw_uploaded' => ['label' => 'RAW Backup', 'count' => $statusCounts['raw_uploaded'] ?? 0],
            'editing' => ['label' => 'In Editing', 'count' => $statusCounts['editing'] ?? 0],
            'completed' => ['label' => 'Completed', 'count' => $statusCounts['completed'] ?? 0],
        ];
        
        $activeFilterCount = 0;
        if (request()->filled('search')) $activeFilterCount++;
        if (request()->filled('event_type')) $activeFilterCount++;
        if (request()->filled('photographer_id')) $activeFilterCount++;
        if (request()->filled('package_id')) $activeFilterCount++;
        if (request()->filled('payment_status')) $activeFilterCount++;
        if (request()->filled('date_range')) $activeFilterCount++;
        if (request()->filled('telecaller_id')) $activeFilterCount++;
        if (request()->filled('sort') && request('sort') !== 'shoot_date_asc') $activeFilterCount++;
    @endphp

    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs no-scrollbar">
        @foreach($statusTabs as $key => $tab)
            <a 
                href="{{ route('shoots.index', array_merge(request()->except(['page']), ['status' => $key])) }}" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-medium whitespace-nowrap transition-colors {{ $currentStatus === (string)$key ? 'bg-indigo-50 text-[#4F46E5] font-semibold border border-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}"
            >
                <span>{{ $tab['label'] }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $currentStatus === (string)$key ? 'bg-indigo-200/60 text-indigo-800' : 'bg-slate-100 text-slate-500' }}">
                    {{ $tab['count'] }}
                </span>
            </a>
        @endforeach
    </div>

    <!-- Shoots Table Card with Comprehensive Filters -->
    <x-card :noPadding="true">
        <!-- Main Filter Bar -->
        <form method="GET" action="{{ route('shoots.index') }}" id="shoots-filter-form" class="p-3.5 bg-slate-50/60 border-b border-slate-100 space-y-3">
            <!-- Retain Status Tab in Form -->
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <!-- Top Row: Full-text Search & Primary Controls -->
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
                <!-- Search Input -->
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <x-icon name="search" class="w-4 h-4" />
                    </div>
                    <input 
                        type="text" 
                        name="search" 
                        id="search-input"
                        value="{{ request('search') }}" 
                        placeholder="Search by Shoot ID, client name, phone, title, venue, or telecaller name..." 
                        class="w-full pl-9 pr-9 py-2 text-xs bg-white rounded-lg border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15 transition-all"
                    />
                    @if(request('search'))
                        <a 
                            href="{{ route('shoots.index', request()->except(['search', 'page'])) }}" 
                            title="Clear search" 
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600"
                        >
                            <x-icon name="x" class="w-3.5 h-3.5" />
                        </a>
                    @endif
                </div>

                <!-- Fast Filter Controls -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Sort By -->
                    <div class="flex items-center gap-1.5 bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs">
                        <x-icon name="arrow-up-down" class="w-3.5 h-3.5 text-slate-400" />
                        <select name="sort" onchange="this.form.submit()" class="text-xs bg-transparent border-0 focus:ring-0 text-slate-700 font-medium cursor-pointer focus:outline-hidden">
                            <option value="shoot_date_desc" @selected(request('sort', 'shoot_date_desc') === 'shoot_date_desc')>Date: Latest First (Desc)</option>
                            <option value="shoot_date_asc" @selected(request('sort') === 'shoot_date_asc')>Date: Earliest First</option>
                            <option value="created_at_desc" @selected(request('sort') === 'created_at_desc')>Recently Booked</option>
                            <option value="total_amount_desc" @selected(request('sort') === 'total_amount_desc')>Value: High to Low</option>
                            <option value="balance_amount_desc" @selected(request('sort') === 'balance_amount_desc')>Balance Due: High to Low</option>
                        </select>
                    </div>

                    <!-- Filter Action Button -->
                    <x-button variant="primary" size="sm" type="submit" icon="filter">
                        Filter
                    </x-button>

                    @if($activeFilterCount > 0 || request('status'))
                        <x-button variant="secondary" size="sm" href="{{ route('shoots.index') }}" icon="rotate-ccw">
                            Reset All
                        </x-button>
                    @endif
                </div>
            </div>

            <!-- Second Row: Filter Options Dropdowns -->
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-2.5 text-xs">
                <!-- 1. Event Type Filter -->
                <div class="relative">
                    <select 
                        name="event_type" 
                        onchange="this.form.submit()"
                        class="w-full text-xs bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 pr-8 text-slate-700 appearance-none focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15 transition-all"
                    >
                        <option value="">All Event Types</option>
                        @foreach($eventTypes as $type)
                            <option value="{{ $type }}" @selected(request('event_type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                        <x-icon name="chevron-down" class="w-3.5 h-3.5" />
                    </div>
                </div>

                <!-- 2. Assigned Crew Filter -->
                <div class="relative">
                    <select 
                        name="photographer_id" 
                        onchange="this.form.submit()"
                        class="w-full text-xs bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 pr-8 text-slate-700 appearance-none focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15 transition-all"
                    >
                        <option value="">All Photographers</option>
                        @foreach($photographers as $ph)
                            <option value="{{ $ph->id }}" @selected(request('photographer_id') == $ph->id)>{{ $ph->name }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                        <x-icon name="chevron-down" class="w-3.5 h-3.5" />
                    </div>
                </div>

                <!-- 3. Payment Balance Filter -->
                <div class="relative">
                    <select 
                        name="payment_status" 
                        onchange="this.form.submit()"
                        class="w-full text-xs bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 pr-8 text-slate-700 appearance-none focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15 transition-all"
                    >
                        <option value="">All Payment States</option>
                        <option value="due" @selected(request('payment_status') === 'due')>Due Balance Only</option>
                        <option value="paid" @selected(request('payment_status') === 'paid')>Fully Settled (Paid)</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                        <x-icon name="chevron-down" class="w-3.5 h-3.5" />
                    </div>
                </div>

                <!-- 4. Timeline / Date Filter -->
                <div class="relative">
                    <select 
                        name="date_range" 
                        onchange="this.form.submit()"
                        class="w-full text-xs bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 pr-8 text-slate-700 appearance-none focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15 transition-all"
                    >
                        <option value="">All Dates</option>
                        <option value="today" @selected(request('date_range') === 'today')>Today's Shoots</option>
                        <option value="this_week" @selected(request('date_range') === 'this_week')>This Week</option>
                        <option value="this_month" @selected(request('date_range') === 'this_month')>This Month</option>
                        <option value="upcoming" @selected(request('date_range') === 'upcoming')>All Upcoming</option>
                        <option value="past" @selected(request('date_range') === 'past')>Past Events</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                        <x-icon name="chevron-down" class="w-3.5 h-3.5" />
                    </div>
                </div>

                <!-- 5. Telecaller Filter (Visible to Admin/All non-restricted) -->
                <div class="relative">
                    <select 
                        name="telecaller_id" 
                        onchange="this.form.submit()"
                        @if(auth()->user()?->isTelecaller()) disabled @endif
                        class="w-full text-xs bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 pr-8 text-slate-700 appearance-none focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15 transition-all @if(auth()->user()?->isTelecaller()) opacity-70 bg-slate-100 cursor-not-allowed @endif"
                    >
                        @if(auth()->user()?->isTelecaller())
                            <option value="{{ auth()->id() }}" selected>Telecaller: You ({{ auth()->user()->name }})</option>
                        @else
                            <option value="">All Telecallers</option>
                            @foreach($telecallers ?? [] as $tc)
                                <option value="{{ $tc->id }}" @selected(request('telecaller_id') == $tc->id)>Telecaller: {{ $tc->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                        <x-icon name="chevron-down" class="w-3.5 h-3.5" />
                    </div>
                </div>
            </div>

            <!-- Active Filter Chips Row -->
            @if($activeFilterCount > 0)
                <div class="pt-2 flex flex-wrap items-center gap-1.5 text-[11px]">
                    <span class="text-slate-400 font-medium mr-1">Active filters:</span>

                    @if(request('search'))
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 border border-indigo-200 text-indigo-700">
                            <span>Search: <strong>"{{ request('search') }}"</strong></span>
                            <a href="{{ route('shoots.index', request()->except(['search', 'page'])) }}" class="hover:text-indigo-900">&times;</a>
                        </span>
                    @endif

                    @if(request('event_type'))
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 border border-indigo-200 text-indigo-700">
                            <span>Event: <strong>{{ request('event_type') }}</strong></span>
                            <a href="{{ route('shoots.index', request()->except(['event_type', 'page'])) }}" class="hover:text-indigo-900">&times;</a>
                        </span>
                    @endif

                    @if(request('photographer_id'))
                        @php $phName = $photographers->firstWhere('id', request('photographer_id'))?->name; @endphp
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 border border-indigo-200 text-indigo-700">
                            <span>Photographer: <strong>{{ $phName ?? 'Selected' }}</strong></span>
                            <a href="{{ route('shoots.index', request()->except(['photographer_id', 'page'])) }}" class="hover:text-indigo-900">&times;</a>
                        </span>
                    @endif

                    @if(request('payment_status'))
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 border border-indigo-200 text-indigo-700">
                            <span>Payment: <strong>{{ request('payment_status') === 'due' ? 'Due Balances Only' : 'Fully Paid' }}</strong></span>
                            <a href="{{ route('shoots.index', request()->except(['payment_status', 'page'])) }}" class="hover:text-indigo-900">&times;</a>
                        </span>
                    @endif

                    @if(request('date_range'))
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 border border-indigo-200 text-indigo-700">
                            <span>Date: <strong>{{ ucwords(str_replace('_', ' ', request('date_range'))) }}</strong></span>
                            <a href="{{ route('shoots.index', request()->except(['date_range', 'page'])) }}" class="hover:text-indigo-900">&times;</a>
                        </span>
                    @endif

                    @if(request('telecaller_id'))
                        @php $tcName = collect($telecallers ?? [])->firstWhere('id', request('telecaller_id'))?->name; @endphp
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 border border-amber-200 text-amber-800">
                            <span>Telecaller: <strong>{{ $tcName ?? 'Selected' }}</strong></span>
                            <a href="{{ route('shoots.index', request()->except(['telecaller_id', 'page'])) }}" class="hover:text-amber-950">&times;</a>
                        </span>
                    @endif

                    <a href="{{ route('shoots.index', request()->only(['status'])) }}" class="text-[#4F46E5] hover:underline font-semibold ml-1">
                        Clear all filters
                    </a>
                </div>
            @endif
        </form>

        <!-- Table Display -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4 first:pl-5">Shoot ID & Title</th>
                        <th class="py-3 px-4">Client Contact</th>
                        <th class="py-3 px-4">Package</th>
                        <th class="py-3 px-4">Crew Assigned</th>
                        <th class="py-3 px-4">Date & Venue</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Financials</th>
                        <th class="py-3 px-4 text-right pr-5">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($shoots as $shoot)
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
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4 first:pl-5">
                                <a href="{{ route('shoots.show', $shoot) }}" class="font-bold text-slate-900 hover:text-[#4F46E5] flex items-center gap-1.5">
                                    <span class="font-mono text-indigo-600 text-[11px]">{{ $shoot->shoot_number }}</span>
                                </a>
                                <div class="font-medium text-slate-800 text-[13px] mt-0.5 truncate max-w-xs">{{ $shoot->title }}</div>
                                @php $attribution = $shoot->getTelecallerAttribution(); @endphp
                                @if($attribution['user'])
                                    <div class="mt-1 flex items-center gap-1">
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200/60" title="{{ $attribution['label'] }}: {{ $attribution['user']->name }}">
                                            <x-icon name="headset" class="w-2.5 h-2.5 text-amber-500" />
                                            <span>{{ $attribution['label'] }}: <strong class="font-semibold">{{ $attribution['user']->name }}</strong></span>
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-900">{{ $shoot->customer?->name }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1.5 font-mono">
                                    <span>{{ $shoot->customer?->phone }}</span>
                                    @if($shoot->customer?->phone)
                                        @php
                                            $cleanPhone = preg_replace('/[^0-9]/', '', $shoot->customer->phone);
                                            if (strlen($cleanPhone) === 10) $cleanPhone = '91' . $cleanPhone;
                                        @endphp
                                        <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" title="WhatsApp Customer" class="text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 p-0.5 rounded transition-colors inline-flex items-center">
                                            <x-icon name="message-circle" class="w-3.5 h-3.5" />
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-medium text-slate-800">{{ $shoot->package?->name ?? $shoot->event_type }}</span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($shoot->assignments->count() > 0)
                                    <div class="flex items-center gap-1.5">
                                        <div class="flex items-center -space-x-1.5 overflow-hidden">
                                            @foreach($shoot->assignments as $assignment)
                                                <div 
                                                    title="{{ $assignment->photographer?->name }}" 
                                                    class="w-6 h-6 rounded-full bg-slate-100 border-2 border-white text-slate-700 text-[10px] font-bold flex items-center justify-center shrink-0 shadow-2xs"
                                                >
                                                    {{ substr($assignment->photographer?->name ?? 'P', 0, 1) }}
                                                </div>
                                            @endforeach
                                        </div>
                                        <span class="text-[11px] text-slate-600 font-medium truncate max-w-[110px]" title="{{ $shoot->assignments->pluck('photographer.name')->join(', ') }}">
                                            {{ $shoot->assignments->first()->photographer?->name }}@if($shoot->assignments->count() > 1)<span class="text-slate-400 font-normal"> +{{ $shoot->assignments->count() - 1 }}</span>@endif
                                        </span>
                                    </div>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">No crew assigned</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-medium text-slate-800">{{ $shoot->shoot_date->format('d M Y') }}</div>
                                <div class="text-[11px] text-slate-400 truncate max-w-[150px]">{{ $shoot->venue }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <x-badge :variant="$variant" :dot="true" size="xs">
                                    {{ $statusLabel }}
                                </x-badge>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900 text-xs">₹{{ number_format($shoot->paid_amount) }} / ₹{{ number_format($shoot->total_amount) }}</div>
                                @if($shoot->balance_amount > 0)
                                    <div class="text-[10px] font-semibold text-amber-600">Due: ₹{{ number_format($shoot->balance_amount) }}</div>
                                @else
                                    <div class="text-[10px] font-semibold text-emerald-600">Fully Paid</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right pr-5 whitespace-nowrap">
                                <div class="inline-flex items-center gap-1 justify-end">
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
                            <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <x-icon name="filter-x" class="w-8 h-8 text-slate-300" />
                                    <p class="text-sm font-semibold text-slate-700">No shoot bookings match your filters</p>
                                    <p class="text-xs text-slate-400">Try adjusting your search keywords, event types, or date range.</p>
                                    @if($activeFilterCount > 0 || request('status'))
                                        <a href="{{ route('shoots.index') }}" class="mt-2 inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-50 text-[#4F46E5] text-xs font-semibold hover:bg-indigo-100 transition-colors">
                                            <x-icon name="rotate-ccw" class="w-3.5 h-3.5" />
                                            <span>Reset All Filters</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($shoots->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">
                {{ $shoots->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
