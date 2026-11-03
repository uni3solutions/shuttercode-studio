@extends('layouts.app', [
    'title' => 'Enquiries & Lead Management',
    'breadcrumbs' => ['Home' => '/', 'Sales' => '#', 'Leads' => route('leads.index')]
])

@section('content')
<div class="space-y-5">
    <!-- Header Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Leads & Enquiries Pipeline</h2>
            <p class="text-xs text-slate-500 mt-0.5">Track WhatsApp Meta leads, telecaller follow-ups, and booking conversions</p>
        </div>
        <div class="flex items-center gap-2.5">
            <x-button variant="primary" size="sm" icon="plus" href="{{ route('leads.create') }}">
                <span>New Enquiry</span>
            </x-button>
        </div>
    </div>

    <!-- Telecaller & Lead Performance Metrics Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        @foreach($leadStats as $stat)
            <x-stat-card 
                :title="$stat['title']" 
                :value="$stat['value']" 
                :trend="$stat['trend']" 
                :trendUp="$stat['trendUp']" 
                :icon="$stat['icon']" 
                :iconColor="$stat['color']" 
                :subtitle="$stat['subtitle'] ?? null"
            />
        @endforeach
    </div>

    <!-- Status Tabs Toolbar with Live Number Counters -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs no-scrollbar">
        @php
            $statuses = [
                '' => 'All Leads',
                'new' => 'New Enquiry',
                'contacted' => 'Contacted',
                'in_discussion' => 'In Discussion',
                'interested' => 'Interested',
                'follow_up_later' => 'Follow Up Later',
                'not_responding' => 'Not Responding',
                'not_interested' => 'Not Interested',
                'converted' => 'Converted',
            ];
            $currentStatus = request('status', '');
        @endphp

        @foreach($statuses as $key => $label)
            @php
                $count = $statusCounts[$key] ?? 0;
            @endphp
            <a 
                href="{{ route('leads.index', array_merge(request()->query(), ['status' => $key, 'page' => 1])) }}" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-medium whitespace-nowrap transition-colors {{ $currentStatus === (string)$key ? 'bg-indigo-50 text-[#4F46E5] font-semibold border border-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}"
            >
                <span>{{ $label }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $currentStatus === (string)$key ? 'bg-indigo-200/70 text-indigo-800' : 'bg-slate-100 text-slate-600' }}">
                    {{ $count }}
                </span>
            </a>
        @endforeach
    </div>

    <!-- Leads Table Card with Single-Line Desktop Toolbar & Fully Mobile-Optimized Layout -->
    <x-card :noPadding="true">
        <form method="GET" action="{{ route('leads.index') }}" id="leads-filter-form" class="p-3 sm:p-3.5 bg-slate-50/70 border-b border-slate-100">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <!-- Unified Single-Line Filter Row on Desktop, Adaptive Stack on Mobile -->
            <div class="flex flex-col lg:flex-row lg:items-center gap-2.5 lg:gap-2">
                <!-- Search Box -->
                <div class="relative flex-1 min-w-[180px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <x-icon name="search" class="w-4 h-4" />
                    </div>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Search name, phone, lead ID, venue..." 
                        class="w-full pl-9 pr-7 py-1.5 text-xs bg-white rounded-lg border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                    />
                    @if(request('search'))
                        <a href="{{ route('leads.index', request()->except(['search', 'page'])) }}" class="absolute inset-y-0 right-0 pr-2 flex items-center text-slate-400 hover:text-slate-600">
                            <x-icon name="x" class="w-3.5 h-3.5" />
                        </a>
                    @endif
                </div>

                <!-- Filters & Controls Toolbar (One single line on desktop, responsive wrap on mobile) -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:flex lg:items-center gap-1.5 text-xs">
                    <!-- Date Field Type -->
                    <select name="date_type" class="w-full lg:w-auto bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-700 focus:outline-hidden focus:border-[#4F46E5] cursor-pointer">
                        <option value="created_at" @selected(($dateType ?? request('date_type', 'created_at')) === 'created_at')>Enquiry Date</option>
                        <option value="event_date" @selected(($dateType ?? request('date_type')) === 'event_date')>Shoot Date</option>
                    </select>

                    <!-- Date Preset -->
                    <select 
                        name="date_preset" 
                        id="leads-date-preset"
                        onchange="handleDatePresetChange(this.value)"
                        class="w-full lg:w-auto bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-700 focus:outline-hidden focus:border-[#4F46E5] cursor-pointer"
                    >
                        <option value="all" @selected(($datePreset ?? request('date_preset')) === 'all' || (!request('date_preset') && !request('date_from') && !request('date_to')))>All Time</option>
                        <option value="today" @selected(($datePreset ?? request('date_preset')) === 'today')>Today</option>
                        <option value="yesterday" @selected(($datePreset ?? request('date_preset')) === 'yesterday')>Yesterday</option>
                        <option value="this_week" @selected(($datePreset ?? request('date_preset')) === 'this_week')>This Week</option>
                        <option value="this_month" @selected(($datePreset ?? request('date_preset')) === 'this_month')>This Month</option>
                        <option value="last_month" @selected(($datePreset ?? request('date_preset')) === 'last_month')>Last Month</option>
                        <option value="custom" @selected(($datePreset ?? request('date_preset')) === 'custom' || (!empty($dateFrom) && ($datePreset ?? request('date_preset')) === 'custom'))>Custom Range</option>
                    </select>

                    <!-- Date Range (From - To) -->
                    <div class="col-span-2 sm:col-span-1 lg:col-auto flex items-center gap-1 bg-white border border-slate-200 rounded-lg px-2 py-1">
                        <input 
                            type="date" 
                            name="date_from" 
                            id="leads-date-from"
                            value="{{ $dateFrom ?? request('date_from') }}" 
                            class="text-xs text-slate-700 border-0 p-0 focus:ring-0 cursor-pointer w-full sm:w-28"
                            title="Start Date"
                        />
                        <span class="text-slate-300 text-xs">&ndash;</span>
                        <input 
                            type="date" 
                            name="date_to" 
                            id="leads-date-to"
                            value="{{ $dateTo ?? request('date_to') }}" 
                            class="text-xs text-slate-700 border-0 p-0 focus:ring-0 cursor-pointer w-full sm:w-28"
                            title="End Date"
                        />
                    </div>

                    @if(!auth()->user()->isTelecaller())
                        <select name="created_by" onchange="this.form.submit()" class="w-full lg:w-auto bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-700 font-medium focus:outline-hidden focus:border-[#4F46E5] cursor-pointer">
                            <option value="">All Telecallers</option>
                            @foreach($telecallers as $t)
                                <option value="{{ $t->id }}" @selected(request('created_by') == $t->id)>
                                    {{ $t->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                    <select name="source" onchange="this.form.submit()" class="w-full lg:w-auto bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-700 font-medium focus:outline-hidden focus:border-[#4F46E5] cursor-pointer">
                        <option value="">All Sources</option>
                        <option value="WhatsApp Meta" @selected(request('source') === 'WhatsApp Meta')>WhatsApp</option>
                        <option value="Website Form" @selected(request('source') === 'Website Form')>Website</option>
                        <option value="Instagram DM" @selected(request('source') === 'Instagram DM')>Instagram</option>
                        <option value="Walk-in" @selected(request('source') === 'Walk-in')>Walk-in</option>
                    </select>

                    <div class="col-span-2 sm:col-span-1 lg:col-auto flex items-center gap-1.5">
                        <button type="submit" class="flex-1 lg:flex-none inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-lg bg-[#4F46E5] hover:bg-[#4338CA] text-white text-xs font-semibold shadow-2xs transition cursor-pointer">
                            <x-icon name="filter" class="w-3.5 h-3.5" />
                            <span>Filter</span>
                        </button>

                        @if(request('search') || request('source') || request('created_by') || request('date_preset') || request('date_from') || request('date_to'))
                            <a 
                                href="{{ route('leads.index', request('status') ? ['status' => request('status')] : []) }}" 
                                class="inline-flex items-center justify-center p-1.5 rounded-lg border border-slate-200 bg-white text-slate-500 hover:text-rose-600 hover:bg-slate-50 transition"
                                title="Reset all filters"
                            >
                                <x-icon name="rotate-ccw" class="w-3.5 h-3.5" />
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        <script>
            function handleDatePresetChange(preset) {
                const fromInput = document.getElementById('leads-date-from');
                const toInput = document.getElementById('leads-date-to');
                const now = new Date();
                
                const formatDate = (d) => {
                    const year = d.getFullYear();
                    const month = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                };

                if (preset === 'today') {
                    fromInput.value = formatDate(now);
                    toInput.value = formatDate(now);
                } else if (preset === 'yesterday') {
                    const y = new Date();
                    y.setDate(y.getDate() - 1);
                    fromInput.value = formatDate(y);
                    toInput.value = formatDate(y);
                } else if (preset === 'this_week') {
                    const firstDay = new Date(now);
                    const day = now.getDay() || 7;
                    firstDay.setDate(now.getDate() - day + 1);
                    const lastDay = new Date(firstDay);
                    lastDay.setDate(firstDay.getDate() + 6);
                    fromInput.value = formatDate(firstDay);
                    toInput.value = formatDate(lastDay);
                } else if (preset === 'this_month') {
                    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                    fromInput.value = formatDate(firstDay);
                    toInput.value = formatDate(lastDay);
                } else if (preset === 'last_month') {
                    const firstDay = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                    const lastDay = new Date(now.getFullYear(), now.getMonth(), 0);
                    fromInput.value = formatDate(firstDay);
                    toInput.value = formatDate(lastDay);
                } else if (preset === 'all') {
                    fromInput.value = '';
                    toInput.value = '';
                }
            }
        </script>

        <!-- Mobile Card View (optimized specifically for phone screens) -->
        <div class="divide-y divide-slate-100 md:hidden">
            @forelse($leads as $lead)
                <div class="p-3.5 space-y-2.5 hover:bg-slate-50/60 transition-colors">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-mono text-indigo-600 text-[11px] font-bold">{{ $lead->lead_number }}</span>
                                <span class="text-slate-300">&bull;</span>
                                <a href="{{ route('leads.show', $lead) }}" class="font-bold text-slate-900 text-xs hover:text-[#4F46E5] truncate">
                                    {{ $lead->customer_name }}
                                </a>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">
                                {{ $lead->phone }} &bull; {{ $lead->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <x-badge :variant="$lead->status_variant" :dot="true" size="xs">
                            {{ $lead->status_label }}
                        </x-badge>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-[11px] bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                        <div>
                            <span class="text-slate-400 block text-[10px] font-medium">Event & Date</span>
                            <span class="font-semibold text-slate-800 truncate block">{{ $lead->event_type }}</span>
                            @if($lead->event_date)
                                <span class="text-indigo-600 font-medium text-[10px] block mt-0.5">{{ $lead->event_date->format('d M Y') }}</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] font-medium">Est. Budget</span>
                            <span class="font-bold text-slate-900 block">
                                {{ $lead->estimated_budget ? '₹'.number_format($lead->estimated_budget) : '—' }}
                            </span>
                            <span class="text-[10px] text-slate-500 truncate block mt-0.5">{{ $lead->source }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-slate-500 pt-0.5">
                        <div class="truncate mr-2">
                            <span class="text-slate-400">Assigned: </span>
                            <strong class="text-slate-700 font-semibold">{{ $lead->assignedUser?->name ?? 'Unassigned' }}</strong>
                        </div>
                        <div class="inline-flex items-center gap-1.5 shrink-0">
                            <x-whatsapp-button :url="$lead->whatsAppUrl" />
                            <x-button variant="secondary" size="xs" href="{{ route('leads.show', $lead) }}" icon="eye">
                                Details
                            </x-button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-10 text-center text-slate-400">
                    <x-icon name="message-square-quote" class="w-8 h-8 text-slate-300 mx-auto mb-2" />
                    <p class="text-xs font-semibold text-slate-700">No enquiries found</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Try clearing filters or add a new enquiry.</p>
                </div>
            @endforelse
        </div>

        <!-- Desktop & Tablet Full Table View (visible on md+) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4 first:pl-5">Lead ID & Prospect</th>
                        <th class="py-3 px-4">Event Details</th>
                        <th class="py-3 px-4">Budget</th>
                        <th class="py-3 px-4">Source</th>
                        <th class="py-3 px-4">Created By</th>
                        <th class="py-3 px-4">Assigned To</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right pr-5">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($leads as $lead)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 first:pl-5">
                                <a href="{{ route('leads.show', $lead) }}" class="font-bold text-slate-900 hover:text-[#4F46E5] flex items-center gap-1.5">
                                    <span class="font-mono text-indigo-600 text-[11px]">{{ $lead->lead_number }}</span>
                                    <span>&bull; {{ $lead->customer_name }}</span>
                                </a>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $lead->phone }} &bull; {{ $lead->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-medium text-slate-800">{{ $lead->event_type }}</span>
                                @if($lead->event_date)
                                    <div class="text-[11px] text-slate-400 mt-0.5">Date: {{ $lead->event_date->format('d M Y') }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($lead->estimated_budget)
                                    <span class="font-semibold text-slate-900">₹{{ number_format($lead->estimated_budget) }}</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1 text-[11px] text-slate-700 font-medium">
                                    @if(str_contains($lead->source, 'WhatsApp'))
                                        <x-icon name="message-square" class="w-3.5 h-3.5 text-emerald-500" />
                                    @elseif(str_contains($lead->source, 'Instagram'))
                                        <x-icon name="instagram" class="w-3.5 h-3.5 text-pink-500" />
                                    @else
                                        <x-icon name="globe" class="w-3.5 h-3.5 text-indigo-500" />
                                    @endif
                                    {{ $lead->source }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($lead->creator)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-semibold text-[11px] border border-indigo-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                        {{ $lead->creator->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[11px]">System / Web</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-medium text-slate-700">{{ $lead->assignedUser?->name ?? 'Unassigned' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <x-badge :variant="$lead->status_variant" :dot="true">
                                    {{ $lead->status_label }}
                                </x-badge>
                            </td>
                            <td class="py-3.5 px-4 text-right pr-5 whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <x-whatsapp-button :url="$lead->whatsAppUrl" />
                                    <x-button variant="secondary" size="xs" href="{{ route('leads.show', $lead) }}" icon="eye">
                                        Details
                                    </x-button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <x-icon name="message-square-quote" class="w-8 h-8 text-slate-300" />
                                    <p class="text-sm font-medium text-slate-600">No enquiries found</p>
                                    <p class="text-xs text-slate-400">Record a new lead or wait for incoming WhatsApp webhooks.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leads->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">
                {{ $leads->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
