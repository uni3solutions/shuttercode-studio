@extends('layouts.app', [
    'title' => 'Book New Shoot',
    'breadcrumbs' => ['Home' => '/', 'Shoots' => route('shoots.index'), 'New Booking' => '#']
])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Schedule New Shoot Booking</h2>
            <p class="text-xs text-slate-500 mt-0.5">Select a customer, apply package specs, and set up financial terms</p>
        </div>
        <x-button variant="secondary" size="sm" icon="arrow-left" href="{{ route('shoots.index') }}">
            Back to Calendar
        </x-button>
    </div>

    <x-card>
        <form action="{{ route('shoots.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Client & Package Details -->
            <div>
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3">1. Client & Photography Package</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-select label="Select Customer" name="customer_id" required>
                            <option value="">-- Choose Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" @selected($selectedCustomerId == $c->id)>
                                    {{ $c->name }} ({{ $c->phone }})
                                </option>
                            @endforeach
                        </x-select>
                        <div class="mt-1 text-[11px] text-slate-500">
                            New client? <a href="{{ route('customers.create') }}" class="text-[#4F46E5] font-semibold hover:underline">Add to directory first &rarr;</a>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-700">Photography Package</label>
                            @if(auth()->user()?->isTelecaller())
                                <span class="text-[10px] text-indigo-600 font-medium">Your Packages Only</span>
                            @endif
                        </div>
                        <select 
                            name="package_id" 
                            id="package_selector" 
                            onchange="autoFillPackage(this)"
                            class="block w-full rounded-lg border border-slate-300 text-xs py-2 px-3 bg-white focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                        >
                            <option value="">-- Custom Package / None --</option>
                            @foreach($packages as $pkg)
                                <option 
                                    value="{{ $pkg->id }}" 
                                    data-price="{{ $pkg->price }}" 
                                    data-advance="{{ $pkg->advance_amount }}"
                                    data-category="{{ $pkg->category }}"
                                    data-deliverables="{{ $pkg->deliverables }}"
                                    data-photographers="{{ $pkg->photographers_count }}"
                                    data-videographers="{{ $pkg->videographers_count }}"
                                    data-drone="{{ $pkg->drone_included ? 1 : 0 }}"
                                >
                                    {{ $pkg->name }} — ₹{{ number_format($pkg->price) }}
                                </option>
                            @endforeach
                        </select>
                        @if($packages->isEmpty() && auth()->user()?->isTelecaller())
                            <div class="mt-1 text-[11px] text-amber-600 flex items-center gap-1">
                                <x-icon name="alert-circle" class="w-3.5 h-3.5" />
                                <span>No packages created by you yet.</span>
                                <a href="{{ route('packages.create') }}" class="text-[#4F46E5] font-semibold hover:underline">Create a package &rarr;</a>
                            </div>
                        @else
                            <div class="mt-1 text-[11px] text-slate-400">
                                Need a new package? <a href="{{ route('packages.create') }}" class="text-[#4F46E5] font-semibold hover:underline">Add Package &rarr;</a>
                            </div>
                        @endif
                    </div>

                    <div class="sm:col-span-2">
                        <x-input 
                            label="Event / Shoot Title" 
                            name="title" 
                            placeholder="e.g. Rohit & Sneha — Royal Wedding & Stage Reception" 
                            required 
                        />
                    </div>

                    <div>
                        @php
                            $typesList = isset($shootTypes) && $shootTypes->isNotEmpty()
                                ? $shootTypes
                                : \App\Models\ShootType::where('is_active', true)->orderBy('name')->get();
                        @endphp
                        <x-select label="Event Type" name="event_type" id="event_type" required>
                            @foreach($typesList as $type)
                                <option value="{{ $type->name }}" @selected(old('event_type') === $type->name)>{{ $type->name }}</option>
                            @endforeach
                        </x-select>
                        @if(auth()->user()?->isAdmin())
                            <div class="mt-1 flex items-center justify-between text-[11px]">
                                <span class="text-slate-400">Need another shoot type?</span>
                                <a href="{{ route('shoot-types.index') }}" class="text-[#4F46E5] hover:underline font-medium inline-flex items-center gap-1">
                                    <x-icon name="plus" class="w-3 h-3" />
                                    <span>Manage Shoot Types (Admin)</span>
                                </a>
                            </div>
                        @endif
                    </div>

                    <div>
                        <x-input 
                            label="Shoot Date" 
                            name="shoot_date" 
                            type="date" 
                            value="{{ date('Y-m-d') }}" 
                            required 
                        />
                    </div>

                    <div>
                        <x-input 
                            label="Call Time (Start)" 
                            name="start_time" 
                            type="time" 
                            value="10:00" 
                        />
                    </div>

                    <div>
                        <x-input 
                            label="Packup Time (End)" 
                            name="end_time" 
                            type="time" 
                            value="20:00" 
                        />
                    </div>
                </div>
            </div>

            <!-- Venue & Location -->
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3">2. Venue & Logistics</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input 
                            label="Venue Name & Address" 
                            name="venue" 
                            placeholder="e.g. The Leela Palace, Bengaluru" 
                            required 
                            icon="map-pin"
                        />
                    </div>

                    <div>
                        <x-input 
                            label="Google Maps Location URL" 
                            name="location_url" 
                            placeholder="https://maps.google.com/?q=..." 
                            icon="navigation"
                        />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Customer Requirements / Shot List</label>
                        <textarea 
                            name="requirements" 
                            id="requirements_field"
                            rows="2" 
                            placeholder="Key moments to capture, bride getting ready timing, stage decor specs..."
                            class="w-full text-xs rounded-lg border border-slate-300 p-3 placeholder-slate-400 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                        ></textarea>
                    </div>
                </div>
            </div>

            <!-- Financial Terms -->
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3">3. Financial Terms & Advance</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <x-input 
                            label="Total Agreed Amount (₹)" 
                            name="total_amount" 
                            id="total_amount"
                            type="number" 
                            step="0.01" 
                            placeholder="e.g. 150000" 
                            required 
                        />
                    </div>

                    <div>
                        <x-input 
                            label="Discount (₹)" 
                            name="discount_amount" 
                            id="discount_amount"
                            type="number" 
                            step="0.01" 
                            value="0" 
                        />
                    </div>

                    <div>
                        <x-input 
                            label="Advance Deposit (₹)" 
                            name="advance_amount" 
                            id="advance_amount"
                            type="number" 
                            step="0.01" 
                            placeholder="e.g. 50000" 
                        />
                    </div>

                    <div>
                        <x-select label="Advance Payment Method" name="payment_method">
                            <option value="upi">UPI (GPay / PhonePe / Paytm)</option>
                            <option value="bank_transfer">Bank Transfer (NEFT / IMPS)</option>
                            <option value="cash">Cash</option>
                            <option value="card">Credit / Debit Card</option>
                        </x-select>
                    </div>

                    <div class="sm:col-span-2">
                        <x-input 
                            label="Transaction Reference / UTR Number" 
                            name="transaction_reference" 
                            placeholder="e.g. UPI/6628901234 or Bank UTR" 
                        />
                    </div>
                </div>
            </div>

            <!-- Crew Assignment (According to Package Requirements) -->
            <div class="pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                            4. Crew Allocation (According to Requirements Only)
                        </h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Crew options are filtered strictly based on the selected package deliverables &amp; requirements.
                        </p>
                    </div>
                    <span id="crew_requirement_summary" class="text-[11px] font-semibold text-indigo-700 bg-indigo-50 border border-indigo-100 px-2.5 py-1 rounded-md">
                        1 Photographer Required
                    </span>
                </div>

                <!-- Dynamic Crew Slots Container -->
                <div id="crew_slots_container" class="space-y-3">
                    <!-- Populated dynamically via autoFillPackage -->
                </div>
            </div>

            <div class="pt-5 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <x-button variant="secondary" size="md" href="{{ route('shoots.index') }}">
                    Cancel
                </x-button>
                <x-button variant="primary" size="md" type="submit" icon="check">
                    Confirm &amp; Schedule Booking
                </x-button>
            </div>
        </form>
    </x-card>
</div>

<script>
    const teamMembers = @json($photographers ?? []);

    function autoFillPackage(select) {
        const option = select.options[select.selectedIndex];
        
        let photoCount = 1;
        let videoCount = 0;
        let droneIncluded = 0;

        if (option && option.dataset.price) {
            document.getElementById('total_amount').value = option.dataset.price;
            document.getElementById('advance_amount').value = option.dataset.advance;
            
            if (option.dataset.category) {
                const eventType = document.getElementById('event_type');
                for (let i = 0; i < eventType.options.length; i++) {
                    if (eventType.options[i].value.toLowerCase() === option.dataset.category.toLowerCase()) {
                        eventType.selectedIndex = i;
                        break;
                    }
                }
            }

            if (option.dataset.deliverables) {
                document.getElementById('requirements_field').value = option.dataset.deliverables;
            }

            photoCount = parseInt(option.dataset.photographers || '1', 10);
            videoCount = parseInt(option.dataset.videographers || '0', 10);
            droneIncluded = parseInt(option.dataset.drone || '0', 10);
        }

        renderCrewSlots(photoCount, videoCount, droneIncluded);
    }

    function renderCrewSlots(photos, videos, drone) {
        const container = document.getElementById('crew_slots_container');
        const summaryBadge = document.getElementById('crew_requirement_summary');
        
        let summaryParts = [];
        if (photos > 0) summaryParts.push(photos + ' Photographer' + (photos > 1 ? 's' : ''));
        if (videos > 0) summaryParts.push(videos + ' Videographer' + (videos > 1 ? 's' : ''));
        if (drone > 0) summaryParts.push('4K Drone Aerial Pilot');
        
        summaryBadge.textContent = 'Required: ' + summaryParts.join(' • ');

        let html = '';
        let slotIndex = 0;

        // 1. Photographer Slots according to requirement
        for (let i = 1; i <= photos; i++) {
            const role = i === 1 ? 'lead_photographer' : (i === 2 ? 'candid_photographer' : 'traditional_photographer');
            const label = i === 1 ? 'Lead Candid Photographer' : (i === 2 ? 'Second / Candid Photographer' : `Traditional Photographer (${i})`);
            html += generateCrewSlotHtml(slotIndex++, role, label, 'camera', 'photo');
        }

        // 2. Videographer Slots according to requirement (ONLY IF REQUIRED)
        for (let i = 1; i <= videos; i++) {
            const role = 'cinematographer';
            const label = videos === 1 ? 'Lead Cinematographer (Video)' : `Cinematographer ${i} (Video)`;
            html += generateCrewSlotHtml(slotIndex++, role, label, 'video', 'video');
        }

        // 3. Drone Slot (ONLY IF REQUIRED BY PACKAGE)
        if (drone > 0) {
            html += generateCrewSlotHtml(slotIndex++, 'drone_pilot', 'Drone Aerial Pilot (4K)', 'navigation', 'drone');
        }

        container.innerHTML = html;
    }

    function generateCrewSlotHtml(index, roleValue, roleLabel, iconName, type) {
        let optionsHtml = '<option value="">-- Choose ' + roleLabel + ' (Optional) --</option>';
        
        teamMembers.forEach(member => {
            const spec = member.specialization || 'Photographer';
            let isRecommended = false;
            if (type === 'drone' && spec.toLowerCase().includes('drone')) isRecommended = true;
            if (type === 'video' && (spec.toLowerCase().includes('cinema') || spec.toLowerCase().includes('video'))) isRecommended = true;
            if (type === 'photo' && spec.toLowerCase().includes('candid')) isRecommended = true;

            optionsHtml += `<option value="${member.id}">
                ${member.name} (${spec})${isRecommended ? ' ★ Recommended' : ''}
            </option>`;
        });

        return `
            <div class="p-3.5 rounded-xl border border-slate-200/90 bg-slate-50/50 hover:bg-white transition-all space-y-2.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">
                            ${index + 1}
                        </span>
                        <span class="text-xs font-bold text-slate-800">${roleLabel}</span>
                        <span class="text-[10px] uppercase tracking-wider font-semibold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-100">
                            Required Slot
                        </span>
                    </div>
                </div>

                <input type="hidden" name="crew[${index}][role]" value="${roleValue}">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Select Team Artist</label>
                        <select name="crew[${index}][user_id]" class="block w-full rounded-lg border border-slate-300 text-xs py-2 px-3 bg-white focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15">
                            ${optionsHtml}
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Agreed Payout (₹)</label>
                        <input type="number" step="0.01" name="crew[${index}][payout_agreed]" placeholder="e.g. 15000" class="block w-full rounded-lg border border-slate-300 text-xs py-2 px-3 bg-white focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15">
                    </div>
                </div>
            </div>
        `;
    }

    // Initial render on page load
    document.addEventListener('DOMContentLoaded', function() {
        const pkgSelector = document.getElementById('package_selector');
        if (pkgSelector) {
            autoFillPackage(pkgSelector);
        }
    });
</script>
@endsection
