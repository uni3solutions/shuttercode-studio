@extends('layouts.app', [
    'title' => 'Assigned Shoots — Photographer Workspace',
    'breadcrumbs' => ['Home' => '/my-dashboard', 'Assigned Shoots' => '#']
])

@section('content')
<div class="space-y-6">
    <!-- Header with Summary Stats -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Assigned Shoot Bookings</h2>
                <p class="text-xs text-slate-500 mt-0.5">Filter by shoot schedule, pending RAW data uploads, and payout balance</p>
            </div>

            <!-- Financial Summary Quick Stats -->
            <div class="flex items-center gap-3">
                <div class="text-right px-3 py-1 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-[10px] text-slate-400 block uppercase font-medium">Agreed Fees</span>
                    <span class="text-xs font-bold text-slate-900">₹{{ number_format($totalAgreed) }}</span>
                </div>
                <div class="text-right px-3 py-1 bg-emerald-50 rounded-xl border border-emerald-100">
                    <span class="text-[10px] text-emerald-700 block uppercase font-medium">Agency Paid</span>
                    <span class="text-xs font-bold text-emerald-700">₹{{ number_format($totalPaid) }}</span>
                </div>
                <div class="text-right px-3 py-1 bg-amber-50 rounded-xl border border-amber-100">
                    <span class="text-[10px] text-amber-700 block uppercase font-medium">Balance Rest</span>
                    <span class="text-xs font-bold text-amber-700">₹{{ number_format($balanceLeft) }}</span>
                </div>
            </div>
        </div>

        <!-- Filter Categories Tabs -->
        <div class="pt-4 flex flex-wrap items-center gap-2">
            @php
                $tabConfig = [
                    'all' => ['label' => 'All Shoots', 'icon' => 'layers', 'count' => $counts['all']],
                    'today' => ['label' => 'Upcoming Today', 'icon' => 'calendar', 'count' => $counts['today']],
                    'upcoming' => ['label' => 'Scheduled', 'icon' => 'clock', 'count' => $counts['upcoming']],
                    'data_pending' => ['label' => 'Data Submission Pending', 'icon' => 'upload', 'count' => $counts['data_pending']],
                    'payment_due' => ['label' => 'Data Submitted • Payment Rest', 'icon' => 'credit-card', 'count' => $counts['payment_due']],
                    'completed' => ['label' => 'Completed', 'icon' => 'check-circle', 'count' => $counts['completed']],
                ];
            @endphp

            @foreach($tabConfig as $catKey => $tab)
                @php $isActive = ($activeCategory === $catKey); @endphp
                <a 
                    href="{{ route('photographers.portal', ['category' => $catKey]) }}" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs transition-all {{ $isActive ? 'bg-[#4F46E5] text-white font-semibold shadow-xs border border-[#4F46E5]' : 'bg-slate-100 hover:bg-slate-200/90 text-slate-700 hover:text-slate-900 font-medium border border-slate-200/70' }}"
                >
                    <x-icon :name="$tab['icon']" class="w-3.5 h-3.5 {{ $isActive ? 'text-white' : 'text-slate-500' }}" />
                    <span>{{ $tab['label'] }}</span>
                    <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold {{ $isActive ? 'bg-white/20 text-white' : ($catKey === 'data_pending' && $tab['count'] > 0 ? 'bg-amber-100 text-amber-800 border border-amber-300' : ($catKey === 'payment_due' && $tab['count'] > 0 ? 'bg-indigo-100 text-indigo-700 border border-indigo-200' : 'bg-white text-slate-700 border border-slate-200')) }}">
                        {{ $tab['count'] }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Assigned Shoots List -->
    <div class="space-y-4">
        @forelse($assignments as $assignment)
            @php 
                $shoot = $assignment->shoot; 
                $del = $shoot->delivery;
                $studioPhone = preg_replace('/[^0-9]/', '', $studioSettings->phone ?? '919845000002');
                $waMessage = urlencode("Hi Studio Team, here is the RAW Drive link for Shoot {$shoot->shoot_number} ({$shoot->customer?->name}): " . ($del?->raw_drive_link ?: '[Enter Link Here]') . ". Total RAWs shot: " . ($del?->raw_photos_count ?? '0') . ". Please update the studio portal.");
            @endphp
            <x-card>
                <!-- Card Header -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">
                                {{ $shoot->shoot_number }}
                            </span>
                            <h4 class="text-base font-bold text-slate-900">{{ $shoot->title }}</h4>
                            <x-badge variant="info" size="xs">{{ $shoot->event_type }}</x-badge>
                            
                            @if($shoot->status === 'in_progress')
                                <x-badge variant="success" size="xs">Live On Shoot</x-badge>
                            @elseif($shoot->status === 'raw_uploaded')
                                <x-badge variant="info" size="xs">RAW Ingested</x-badge>
                            @elseif($shoot->status === 'completed')
                                <x-badge variant="neutral" size="xs">Completed</x-badge>
                            @else
                                <x-badge variant="warning" size="xs">{{ ucwords(str_replace('_', ' ', $shoot->status)) }}</x-badge>
                            @endif
                        </div>

                        <!-- Customer & Timing Subtitle -->
                        <div class="text-xs text-slate-500 mt-2 flex flex-wrap items-center gap-x-4 gap-y-1">
                            <span class="flex items-center gap-1.5 font-semibold text-slate-800">
                                <x-icon name="user" class="w-3.5 h-3.5 text-slate-400" />
                                Customer: {{ $shoot->customer?->name }}
                            </span>
                            <span>&bull;</span>
                            <span class="flex items-center gap-1.5 text-slate-600">
                                <x-icon name="calendar" class="w-3.5 h-3.5 text-indigo-500" />
                                {{ $shoot->shoot_date?->format('l, d M Y') }}
                                @if($shoot->start_time)
                                    <span class="font-medium text-slate-800">({{ date('h:i A', strtotime($shoot->start_time)) }}@if($shoot->end_time) – {{ date('h:i A', strtotime($shoot->end_time)) }}@endif)</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- Fixed Rate & Payout Breakdown for this Shoot -->
                    <div class="flex items-center gap-3 bg-slate-50 p-2.5 rounded-xl border border-slate-100 shrink-0">
                        <div class="text-right px-2 border-r border-slate-200">
                            <span class="text-[10px] text-slate-400 block uppercase font-medium">Agreed Fee</span>
                            <span class="text-sm font-bold text-slate-900">₹{{ number_format($assignment->payout_agreed) }}</span>
                        </div>
                        <div class="text-right px-2 border-r border-slate-200">
                            <span class="text-[10px] text-emerald-600 block uppercase font-medium">Agency Paid</span>
                            <span class="text-sm font-bold text-emerald-600">₹{{ number_format($assignment->amount_paid) }}</span>
                        </div>
                        <div class="text-right px-2">
                            <span class="text-[10px] text-amber-600 block uppercase font-medium">Balance Rest</span>
                            <span class="text-sm font-bold {{ $assignment->balance_due > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                                ₹{{ number_format($assignment->balance_due) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Shoot Location & Assignment Notes -->
                <div class="py-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs border-b border-slate-100">
                    <!-- Venue & Address -->
                    <div class="space-y-1.5">
                        <span class="text-slate-400 font-semibold uppercase tracking-wider block text-[10px]">Shoot Venue & Address</span>
                        <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-100">
                            <div class="flex items-start gap-2">
                                <x-icon name="map-pin" class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" />
                                <div>
                                    <p class="font-semibold text-slate-800 text-xs">{{ $shoot->venue }}</p>
                                    @if($shoot->location_url)
                                        <a href="{{ $shoot->location_url }}" target="_blank" class="inline-flex items-center gap-1 text-[#4F46E5] font-semibold hover:underline mt-1">
                                            <span>Open in Google Maps</span>
                                            <x-icon name="external-link" class="w-3 h-3" />
                                        </a>
                                    @else
                                        <a href="https://maps.google.com/?q={{ urlencode($shoot->venue) }}" target="_blank" class="inline-flex items-center gap-1 text-[#4F46E5] font-semibold hover:underline mt-1">
                                            <span>Search Map & Directions</span>
                                            <x-icon name="external-link" class="w-3 h-3" />
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Role & Special Instructions -->
                    <div class="space-y-1.5">
                        <span class="text-slate-400 font-semibold uppercase tracking-wider block text-[10px]">Your Role & Company Notes</span>
                        <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 capitalize">{{ str_replace('_', ' ', $assignment->role) }}</span>
                                <x-badge variant="neutral" size="xs">Assigned</x-badge>
                            </div>
                            <p class="mt-1 text-slate-600 leading-relaxed">
                                {{ $assignment->notes ?: ($shoot->requirements ?: 'Standard studio gear checklist. Reach venue 30 minutes before call time.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Delivery Submission & WhatsApp Action Strip -->
                <div class="pt-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-400">RAW Upload Status:</span>
                        @if($del?->raw_drive_link)
                            <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                <x-icon name="check-circle" class="w-3.5 h-3.5 text-emerald-600" />
                                RAW Drive Uploaded ({{ $del->raw_photos_count ?? 0 }} photos)
                            </span>
                            <a href="{{ $del->raw_drive_link }}" target="_blank" class="text-indigo-600 hover:underline font-semibold ml-1">
                                Open Drive &rarr;
                            </a>
                        @else
                            <span class="inline-flex items-center gap-1 text-amber-700 font-medium bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                <x-icon name="clock" class="w-3.5 h-3.5 text-amber-500" />
                                Pending Drive Link
                            </span>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- 1-Click WhatsApp to Team with Drive Link -->
                        <a 
                            href="https://wa.me/{{ $studioPhone }}?text={{ $waMessage }}" 
                            target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold text-xs border border-emerald-200 transition-colors"
                            title="Share Drive link directly with Studio Team via WhatsApp"
                        >
                            <x-icon name="message-circle" class="w-3.5 h-3.5 text-emerald-600" />
                            <span>Send Link on WhatsApp</span>
                        </a>

                        <!-- Direct Drive Upload Modal Button -->
                        <x-button 
                            variant="primary" 
                            size="sm" 
                            icon="upload-cloud" 
                            onclick="document.getElementById('modal-delivery-{{ $shoot->id }}').classList.remove('hidden')"
                        >
                            {{ $del?->raw_drive_link ? 'Update Drive Link' : 'Upload Drive Link' }}
                        </x-button>
                    </div>
                </div>

                <!-- Modal for Uploading RAW Drive Link -->
                <x-modal id="modal-delivery-{{ $shoot->id }}" title="Submit RAW Drive Link" subtitle="Upload Google Drive or Dropbox RAW folder link for {{ $shoot->shoot_number }}">
                    <form action="{{ route('photographers.delivery', $shoot) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <x-input 
                                label="Google Drive / Dropbox RAW Folder Link" 
                                name="raw_drive_link" 
                                type="url" 
                                :value="$del?->raw_drive_link"
                                placeholder="https://drive.google.com/drive/folders/..." 
                                icon="link" 
                                required
                            />
                            <p class="text-[11px] text-slate-400 mt-1">Make sure link permissions are set to "Anyone with link can view".</p>
                        </div>

                        <div>
                            <x-input 
                                label="Total RAW Photos Shot" 
                                name="raw_photos_count" 
                                type="number" 
                                :value="$del?->raw_photos_count"
                                placeholder="e.g. 1450" 
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Post-Shoot Notes / Highlights</label>
                            <textarea 
                                name="notes" 
                                rows="2" 
                                placeholder="Any damaged gear, lighting notes, or special highlights for the editing team..."
                                class="w-full text-xs rounded-lg border border-slate-300 p-2.5 placeholder-slate-400 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                            >{{ old('notes', $del?->notes) }}</textarea>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                            <x-button variant="secondary" size="sm" onclick="document.getElementById('modal-delivery-{{ $shoot->id }}').classList.add('hidden')">
                                Cancel
                            </x-button>
                            <x-button variant="primary" size="sm" type="submit" icon="check">
                                Submit RAW Drive Link
                            </x-button>
                        </div>
                    </form>
                </x-modal>
            </x-card>
        @empty
            <x-card>
                <div class="text-center py-12 text-slate-400">
                    <x-icon name="camera" class="w-10 h-10 mx-auto text-slate-300 mb-2" />
                    <p class="text-sm font-semibold text-slate-700">No Shoots in this Category</p>
                    <p class="text-xs text-slate-400 mt-1">Select another filter tab or review your upcoming schedule.</p>
                </div>
            </x-card>
        @endforelse
    </div>
</div>
@endsection
