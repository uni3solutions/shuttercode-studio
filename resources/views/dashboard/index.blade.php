@extends('layouts.app', [
    'title' => 'Dashboard Overview',
    'breadcrumbs' => ['Home' => '/', 'Operations' => '#', 'Dashboard' => '/']
])

@section('content')
<div class="space-y-6">
    <!-- Welcome Header / Studio Greeting -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white rounded-xl p-5 border border-slate-200/80 shadow-2xs">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-[#4F46E5] shrink-0">
                <x-icon name="camera" class="w-6 h-6" />
            </div>
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">ShutterCode Studio Central</h2>
                <p class="text-xs text-slate-500 mt-0.5">Today is {{ date('l, d F Y') }} &bull; Active photography management & crew operations</p>
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <x-button variant="secondary" size="sm" icon="calendar" href="/shoots">
                <span>View Schedule</span>
            </x-button>
            <x-button variant="primary" size="sm" icon="plus" href="/shoots/create">
                <span>Book Shoot</span>
            </x-button>
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        @foreach($stats as $stat)
            <x-stat-card 
                :title="$stat['title']" 
                :value="$stat['value']" 
                :trend="$stat['trend']" 
                :trendUp="$stat['trendUp']" 
                :icon="$stat['icon']" 
                :iconColor="$stat['color']" 
            />
        @endforeach
    </div>

    <!-- Main Two-Column Operations Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Primary Tables (Shoots & Leads) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Shoots Table Card -->
            <x-card :noPadding="true">
                <x-slot:title>
                    <div class="flex items-center gap-2">
                        <span>Today's & Scheduled Shoots</span>
                        <x-badge variant="info" size="xs">Live Database</x-badge>
                    </div>
                </x-slot:title>

                <x-slot:action>
                    <div class="flex items-center gap-2">
                        <x-button variant="ghost" size="xs" icon="arrow-up-right" href="/shoots">
                            View All Shoots
                        </x-button>
                    </div>
                </x-slot:action>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-4 first:pl-5">Shoot & Client</th>
                                <th class="py-3 px-4">Package & Amount</th>
                                <th class="py-3 px-4">Assigned Crew</th>
                                <th class="py-3 px-4">Date & Venue</th>
                                <th class="py-3 px-4">Status</th>
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
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-3 px-4 first:pl-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-indigo-50/80 border border-indigo-100/70 flex items-center justify-center text-[#4F46E5] shrink-0">
                                                <x-icon name="camera" class="w-4 h-4" />
                                            </div>
                                            <div class="min-w-0">
                                                <a href="{{ route('shoots.show', $shoot) }}" class="font-semibold text-slate-900 text-xs hover:text-[#4F46E5] truncate block max-w-xs transition-colors">
                                                    {{ $shoot->title }}
                                                </a>
                                                <div class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-0.5 truncate">
                                                    <span class="font-mono text-indigo-600 font-medium">{{ $shoot->shoot_number }}</span>
                                                    <span>&bull;</span>
                                                    <span class="text-slate-600 font-medium truncate max-w-[130px]">{{ $shoot->customer?->name }}</span>
                                                    @if($shoot->customer?->phone)
                                                        @php
                                                            $cleanPhone = preg_replace('/[^0-9]/', '', $shoot->customer->phone);
                                                            if (strlen($cleanPhone) === 10) $cleanPhone = '91' . $cleanPhone;
                                                        @endphp
                                                        <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" title="WhatsApp {{ $shoot->customer->name }}" class="text-emerald-600 hover:text-emerald-700 hover:scale-110 p-0.5 rounded transition-transform inline-flex items-center">
                                                            <x-icon name="message-circle" class="w-3 h-3" />
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="text-xs font-medium text-slate-800 truncate max-w-[160px]" title="{{ $shoot->package?->name ?? $shoot->event_type }}">
                                            {{ $shoot->package?->name ?? $shoot->event_type }}
                                        </div>
                                        <div class="text-[11px] font-mono mt-0.5 text-slate-400">
                                            <span class="font-bold text-slate-800">₹{{ number_format($shoot->paid_amount) }}</span>
                                            <span>/ ₹{{ number_format($shoot->total_amount) }}</span>
                                        </div>
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
                                            <span class="text-[11px] text-slate-400 italic">Unassigned</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="text-xs font-semibold text-slate-800">{{ $shoot->shoot_date->format('d M Y') }}</div>
                                        <div class="text-[11px] text-slate-400 truncate max-w-[130px] mt-0.5" title="{{ $shoot->venue }}">{{ $shoot->venue }}</div>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <x-badge :variant="$variant" :dot="true" size="xs">
                                            {{ $statusLabel }}
                                        </x-badge>
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
                                    <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                        No shoots found. Click "Book Shoot" to create one.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <x-slot:footer>
                    <span>Showing {{ count($shoots) }} shoots from active calendar</span>
                    <a href="/shoots" class="text-[#4F46E5] font-semibold hover:underline">Open Full Shoot Calendar &rarr;</a>
                </x-slot:footer>
            </x-card>

            <!-- WhatsApp & Web Leads Card -->
            <x-card :noPadding="true">
                <x-slot:title>
                    <div class="flex items-center gap-2">
                        <span>Recent Enquiries & Telecaller Pipeline</span>
                        <x-badge variant="success" size="xs">Live Leads</x-badge>
                    </div>
                </x-slot:title>
                <x-slot:action>
                    <x-button variant="ghost" size="xs" icon="plus" href="/leads/create">
                        Add Enquiry
                    </x-button>
                </x-slot:action>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-4 first:pl-5">Client Prospect</th>
                                <th class="py-3 px-4">Event Details & Budget</th>
                                <th class="py-3 px-4">Source</th>
                                <th class="py-3 px-4">Assigned Telecaller</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right pr-5">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($leads as $lead)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-3 px-4 first:pl-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-emerald-50/80 border border-emerald-100/70 flex items-center justify-center text-emerald-600 shrink-0 text-xs font-bold">
                                                {{ substr($lead->customer_name, 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <a href="{{ route('leads.show', $lead) }}" class="font-semibold text-slate-900 text-xs hover:text-[#4F46E5] truncate block max-w-xs transition-colors">
                                                    {{ $lead->customer_name }}
                                                </a>
                                                <div class="text-[11px] text-slate-400 mt-0.5 truncate">
                                                    {{ $lead->phone }} &bull; {{ $lead->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-medium text-slate-800 text-xs">{{ $lead->event_type }}</div>
                                        <div class="text-[11px] text-indigo-600 font-semibold mt-0.5">Est. ₹{{ number_format($lead->estimated_budget) }}</div>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 text-[11px] text-slate-600">
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
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span class="font-medium text-slate-700 text-xs">{{ $lead->assignedUser?->name ?? 'Unassigned' }}</span>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <x-badge :variant="$lead->status_variant" :dot="true" size="xs">
                                            {{ $lead->status_label }}
                                        </x-badge>
                                    </td>
                                    <td class="py-3 px-4 text-right pr-5 whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5 justify-end">
                                            <x-whatsapp-button :url="$lead->whatsAppUrl" />
                                            <a href="{{ route('leads.show', $lead) }}" title="View Lead Details" class="w-7 h-7 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-[#4F46E5] hover:bg-indigo-50/80 transition-colors">
                                                <x-icon name="eye" class="w-4 h-4" />
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                        No leads recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <!-- Right Column: Operations Sidebar (Roster, Quick Actions, Delivery Progress) -->
        <div class="space-y-6">
            <!-- Quick Actions Panel -->
            <x-card title="Quick Studio Actions">
                <div class="grid grid-cols-2 gap-2.5">
                    <a href="/shoots/create" class="p-3 rounded-lg border border-slate-200/80 hover:border-indigo-300 hover:bg-indigo-50/40 transition-all flex flex-col items-center text-center group">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 group-hover:bg-[#4F46E5] group-hover:text-white flex items-center justify-center transition-colors mb-2">
                            <x-icon name="calendar-plus" class="w-4 h-4" />
                        </div>
                        <span class="text-xs font-semibold text-slate-800">New Booking</span>
                        <span class="text-[10px] text-slate-400 mt-0.5">Schedule shoot</span>
                    </a>

                    <a href="/leads/create" class="p-3 rounded-lg border border-slate-200/80 hover:border-indigo-300 hover:bg-indigo-50/40 transition-all flex flex-col items-center text-center group">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white flex items-center justify-center transition-colors mb-2">
                            <x-icon name="user-plus" class="w-4 h-4" />
                        </div>
                        <span class="text-xs font-semibold text-slate-800">Add Lead</span>
                        <span class="text-[10px] text-slate-400 mt-0.5">Record enquiry</span>
                    </a>

                    <a href="/payments/create" class="p-3 rounded-lg border border-slate-200/80 hover:border-indigo-300 hover:bg-indigo-50/40 transition-all flex flex-col items-center text-center group">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white flex items-center justify-center transition-colors mb-2">
                            <x-icon name="receipt" class="w-4 h-4" />
                        </div>
                        <span class="text-xs font-semibold text-slate-800">Record Pay</span>
                        <span class="text-[10px] text-slate-400 mt-0.5">Customer / Crew</span>
                    </a>

                    <a href="/invoices/create" class="p-3 rounded-lg border border-slate-200/80 hover:border-indigo-300 hover:bg-indigo-50/40 transition-all flex flex-col items-center text-center group">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 group-hover:bg-purple-600 group-hover:text-white flex items-center justify-center transition-colors mb-2">
                            <x-icon name="file-check" class="w-4 h-4" />
                        </div>
                        <span class="text-xs font-semibold text-slate-800">Create Invoice</span>
                        <span class="text-[10px] text-slate-400 mt-0.5">Tax invoice PDF</span>
                    </a>
                </div>
            </x-card>

            <!-- Photographer Roster Today -->
            <x-card title="Photographer Team Roster" subtitle="Crew status & availability">
                <div class="space-y-3">
                    @forelse($photographers as $crew)
                        @php
                            $isAssignedToday = $crew->assignments_count > 0;
                        @endphp
                        <div class="flex items-center justify-between p-2.5 rounded-lg border border-slate-100 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-700">
                                        {{ substr($crew->name, 0, 1) }}
                                    </div>
                                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full {{ $isAssignedToday ? 'bg-amber-500' : 'bg-emerald-500' }} ring-2 ring-white"></span>
                                </div>
                                <div>
                                    <h5 class="text-xs font-semibold text-slate-900">{{ $crew->name }}</h5>
                                    <p class="text-[11px] text-slate-500">{{ $crew->specialization ?? 'Photographer' }}</p>
                                </div>
                            </div>
                            <span class="text-[11px] font-medium {{ $isAssignedToday ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-emerald-700 bg-emerald-50 border-emerald-200' }} px-2 py-0.5 rounded-md border">
                                {{ $isAssignedToday ? 'On Shoot Today' : 'Available' }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">No photographers registered.</p>
                    @endforelse
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400">{{ count($photographers) }} total registered crew</span>
                    <a href="/photographers" class="text-[#4F46E5] font-semibold hover:underline">Manage Team &rarr;</a>
                </div>
            </x-card>

            <!-- Post-Production & Delivery Pipeline -->
            <x-card title="Delivery & Post-Production Queue" subtitle="Photos, videos & album status">
                <div class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-medium text-slate-800">RAW Ingest & Culling</span>
                            <span class="text-slate-500 font-semibold">{{ $deliveryPipeline['raw_uploaded'] }} Shoots</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full bg-indigo-500 rounded-full" style="width: 75%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-medium text-slate-800">Color Grading & Retouching</span>
                            <span class="text-slate-500 font-semibold">{{ $deliveryPipeline['editing'] }} Shoots</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full bg-purple-500 rounded-full" style="width: 50%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-medium text-slate-800">Album Proofing with Client</span>
                            <span class="text-slate-500 font-semibold">{{ $deliveryPipeline['proofing'] }} Shoots</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full bg-amber-500 rounded-full" style="width: 30%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-medium text-slate-800">Delivered Galleries</span>
                            <span class="text-emerald-600 font-semibold">{{ $deliveryPipeline['delivered'] }} Shoots</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full" style="width: 90%"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100">
                    <a href="/invoices" class="flex items-center justify-between text-xs text-[#4F46E5] font-semibold hover:underline">
                        <span>Check Delivery Portal</span>
                        <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                    </a>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection
