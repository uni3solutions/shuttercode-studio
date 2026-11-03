@extends('layouts.app', [
    'title' => 'Photographers Directory',
    'breadcrumbs' => ['Home' => '/', 'Team' => '#', 'Photographers' => route('photographers.index')]
])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Photographers Directory</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage studio photographers, candid specialists, cinematographers, and drone operators</p>
        </div>
        <div class="flex items-center gap-2.5">
            <x-button variant="primary" size="sm" icon="plus" onclick="document.getElementById('modal-add-photographer').classList.remove('hidden')">
                <span>+ Add Photographer</span>
            </x-button>
            <x-button variant="secondary" size="sm" icon="camera" href="/my-shoots">
                <span>Photographer Portal</span>
            </x-button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Total Photographers</p>
                    <p class="text-xl font-bold text-slate-900 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                    <x-icon name="camera" class="w-5 h-5" />
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-2 font-medium">Verified studio shooters & artists</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Active Shoot Assignments</p>
                    <p class="text-xl font-bold text-indigo-600 mt-1">{{ $stats['active_assignments'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600">
                    <x-icon name="layers" class="w-5 h-5" />
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-2 font-medium">Assigned across wedding & events</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Active Status</p>
                    <p class="text-xl font-bold text-emerald-600 mt-1">{{ $stats['active'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                    <x-icon name="check-circle" class="w-5 h-5" />
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-2 font-medium">Ready for on-field shoot dispatch</p>
        </div>
    </div>

    <!-- Search & Filter Toolbar + Tabular View -->
    <x-card :noPadding="true">
        <form method="GET" action="{{ route('photographers.index') }}" class="p-3.5 flex flex-col lg:flex-row lg:items-center justify-between gap-3 bg-slate-50/70 border-b border-slate-200/80">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-1">
                <!-- Search Input with live instant filtering -->
                <div class="relative w-full sm:max-w-xs">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <x-icon name="search" class="w-4 h-4" />
                    </div>
                    <input 
                        type="text" 
                        name="search" 
                        id="photographer-search-input"
                        value="{{ request('search') }}" 
                        placeholder="Search name, phone, email, specialization..." 
                        oninput="filterPhotographersTable(this.value)"
                        class="w-full pl-9 pr-3.5 py-1.5 text-xs bg-white rounded-lg border border-slate-200 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                    />
                </div>

                <!-- Specialization Filter -->
                <div class="w-full sm:w-auto">
                    <select 
                        name="specialization" 
                        onchange="this.form.submit()"
                        class="w-full sm:w-auto px-3 py-1.5 text-xs bg-white rounded-lg border border-slate-200 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15 font-medium text-slate-700"
                    >
                        <option value="">All Specializations</option>
                        @foreach($specializations as $spec)
                            <option value="{{ $spec }}" {{ request('specialization') === $spec ? 'selected' : '' }}>{{ $spec }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Sort Filter -->
                <div class="w-full sm:w-auto">
                    <select 
                        name="sort" 
                        onchange="this.form.submit()"
                        class="w-full sm:w-auto px-3 py-1.5 text-xs bg-white rounded-lg border border-slate-200 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15 font-medium text-slate-700"
                    >
                        <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Sort: Newest Added (Desc)</option>
                        <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Sort: Name (A-Z)</option>
                        <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Sort: Name (Z-A)</option>
                        <option value="shoots_desc" {{ request('sort') === 'shoots_desc' ? 'selected' : '' }}>Sort: Most Shoots Assigned</option>
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 self-end lg:self-auto">
                <x-button variant="primary" size="sm" type="submit" icon="search">
                    Filter
                </x-button>
                @if(request('search') || request('specialization') || request('sort'))
                    <x-button variant="secondary" size="sm" href="{{ route('photographers.index') }}">
                        Clear
                    </x-button>
                @endif
            </div>
        </form>

        <!-- Tabular Data Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse" id="photographers-table">
                <thead class="bg-slate-50/90 border-b border-slate-200/80 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4 first:pl-5">Photographer</th>
                        <th class="py-3 px-4">Specialization</th>
                        <th class="py-3 px-4">Contact Phone</th>
                        <th class="py-3 px-4">Assigned Shoots</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Joined Date</th>
                        <th class="py-3 px-4 text-right pr-5">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700" id="photographers-table-body">
                    @forelse($photographers as $ph)
                        <tr class="hover:bg-slate-50/80 transition-colors photographer-row" data-search="{{ strtolower($ph->name . ' ' . $ph->email . ' ' . $ph->phone . ' ' . $ph->specialization) }}">
                            <!-- Photographer Name & Avatar -->
                            <td class="py-3.5 px-4 first:pl-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs shrink-0">
                                        {{ substr($ph->name, 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-900 tracking-tight truncate">{{ $ph->name }}</p>
                                        <p class="text-[11px] text-slate-400 truncate">{{ $ph->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Specialization Badge -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                                    <x-icon name="camera" class="w-3.5 h-3.5 text-indigo-500" />
                                    <span>{{ $ph->specialization ?? 'Candid Photographer' }}</span>
                                </span>
                            </td>

                            <!-- Contact Phone & WhatsApp -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-slate-800 font-medium">{{ $ph->phone }}</span>
                                    @if($ph->phone)
                                        @php
                                            $cleanPhone = preg_replace('/[^0-9]/', '', $ph->phone);
                                            if (strlen($cleanPhone) === 10) $cleanPhone = '91' . $cleanPhone;
                                        @endphp
                                        <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" title="WhatsApp Message" class="text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 p-1 rounded transition-colors inline-flex items-center">
                                            <x-icon name="message-circle" class="w-3.5 h-3.5" />
                                        </a>
                                    @endif
                                </div>
                            </td>

                            <!-- Shoots Assigned -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <a href="{{ route('shoots.index') }}?photographer_id={{ $ph->id }}" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors" title="View shoots assigned to {{ $ph->name }}">
                                    <span>{{ $ph->assignments_count }} Shoots</span>
                                    <x-icon name="arrow-up-right" class="w-3 h-3" />
                                </a>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Active
                                </span>
                            </td>

                            <!-- Joined Date -->
                            <td class="py-3.5 px-4 whitespace-nowrap text-slate-500">
                                {{ $ph->created_at?->format('d M Y') ?? 'Recently' }}
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right pr-5 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <x-whatsapp-button :phone="$ph->phone" />

                                    <a href="{{ route('switch.role', 'photographer') }}" title="Switch to Photographer View" class="p-1 text-indigo-600 hover:text-indigo-800 rounded-md hover:bg-indigo-50 transition-colors inline-flex items-center">
                                        <x-icon name="external-link" class="w-3.5 h-3.5" />
                                    </a>

                                    <form method="POST" action="{{ route('photographers.destroy', $ph) }}" onsubmit="return confirm('Remove photographer {{ $ph->name }} from studio roster?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Remove Photographer" class="p-1 text-slate-400 hover:text-rose-600 rounded-md hover:bg-rose-50 transition-colors">
                                            <x-icon name="trash-2" class="w-3.5 h-3.5" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="server-empty-row">
                            <td colspan="7" class="py-12 text-center">
                                <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-2">
                                    <x-icon name="camera" class="w-5 h-5" />
                                </div>
                                <h3 class="text-xs font-bold text-slate-800">No Photographers Found</h3>
                                <p class="text-[11px] text-slate-400 mt-0.5">Try adjusting your search terms or filters.</p>
                                <div class="mt-3">
                                    <x-button variant="primary" size="xs" icon="plus" onclick="document.getElementById('modal-add-photographer').classList.remove('hidden')">
                                        Add Photographer
                                    </x-button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    <tr id="client-no-match-row" class="hidden">
                        <td colspan="7" class="py-10 text-center">
                            <p class="text-xs font-bold text-slate-800">No matching photographers found</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Check your spelling or clear the search input.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer Summary -->
        <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
            <div>
                Showing <span class="font-bold text-slate-800" id="visible-photographers-count">{{ $photographers->count() }}</span> of <span class="font-bold text-slate-800">{{ $photographers->total() }}</span> studio photographers
            </div>
            <div class="flex items-center gap-4 text-[11px] text-slate-400">
                <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> {{ $stats['active'] }} Active Photographers</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-indigo-500"></span> {{ $stats['active_assignments'] }} Assigned Shoots</span>
            </div>
        </div>

        @if($photographers->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">
                {{ $photographers->links() }}
            </div>
        @endif
    </x-card>
</div>

<!-- Modal: Add Photographer -->
<x-modal id="modal-add-photographer" title="Add New Photographer" subtitle="Create credentials and profile for studio photographer" maxWidth="md">
    <form method="POST" action="{{ route('photographers.store') }}" class="space-y-4">
        @csrf

        @if($errors->any())
            <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-700 space-y-1">
                @foreach($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Photographer Full Name <span class="text-rose-500">*</span></label>
            <input 
                type="text" 
                name="name" 
                required 
                placeholder="e.g. Rahul Sharma"
                value="{{ old('name') }}"
                class="w-full px-3 py-2 text-xs bg-white rounded-lg border border-slate-200 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
            />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address <span class="text-rose-500">*</span></label>
                <input 
                    type="email" 
                    name="email" 
                    required 
                    placeholder="photographer@studio.com"
                    value="{{ old('email') }}"
                    class="w-full px-3 py-2 text-xs bg-white rounded-lg border border-slate-200 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                />
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Phone / WhatsApp <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    name="phone" 
                    required 
                    placeholder="9876543210"
                    value="{{ old('phone') }}"
                    class="w-full px-3 py-2 text-xs bg-white rounded-lg border border-slate-200 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                />
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Photography Specialization</label>
            <select 
                name="specialization" 
                class="w-full px-3 py-2 text-xs bg-white rounded-lg border border-slate-200 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
            >
                <option value="Candid Wedding Photographer">Candid Wedding Photographer</option>
                <option value="Traditional Cinematographer">Traditional Cinematographer</option>
                <option value="Drone Operator & Aerial Specialist">Drone Operator & Aerial Specialist</option>
                <option value="Lead Event Photographer">Lead Event Photographer</option>
                <option value="Fashion & Portrait Specialist">Fashion & Portrait Specialist</option>
                <option value="Baby & Maternity Specialist">Baby & Maternity Specialist</option>
                <option value="Video Editor & Colorist">Video Editor & Colorist</option>
                <option value="Album Designer">Album Designer</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Login Password</label>
            <input 
                type="password" 
                name="password" 
                placeholder="Leave blank for default: password"
                class="w-full px-3 py-2 text-xs bg-white rounded-lg border border-slate-200 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
            />
            <p class="text-[11px] text-slate-400 mt-1">Default temporary password is <code>password</code> if left empty.</p>
        </div>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
            <x-button variant="secondary" size="sm" type="button" onclick="document.getElementById('modal-add-photographer').classList.add('hidden')">
                Cancel
            </x-button>
            <x-button variant="primary" size="sm" type="submit" icon="user-plus">
                Save Photographer
            </x-button>
        </div>
    </form>
</x-modal>

<script>
    function filterPhotographersTable(query) {
        const term = query.toLowerCase().trim();
        const rows = document.querySelectorAll('.photographer-row');
        const noMatchRow = document.getElementById('client-no-match-row');
        const countEl = document.getElementById('visible-photographers-count');
        let visibleCount = 0;

        rows.forEach(row => {
            const data = row.getAttribute('data-search') || '';
            if (term === '' || data.includes(term)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (countEl) {
            countEl.textContent = visibleCount;
        }

        if (noMatchRow) {
            if (visibleCount === 0 && rows.length > 0) {
                noMatchRow.classList.remove('hidden');
            } else {
                noMatchRow.classList.add('hidden');
            }
        }
    }
</script>
@endsection
