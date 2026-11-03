@extends('layouts.app', [
    'title' => 'Studio Crew & Support Staff',
    'breadcrumbs' => ['Home' => '/', 'Team' => '#', 'Crew & Staff' => route('crew.index')]
])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Studio Crew & Operations Staff</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage telecallers, client support executives, and operations team members who run daily studio workflows</p>
        </div>
        <div class="flex items-center gap-2.5">
            <x-button variant="primary" size="sm" icon="user-plus" onclick="document.getElementById('modal-add-crew').classList.remove('hidden')">
                <span>+ Add Crew Member</span>
            </x-button>
        </div>
    </div>

    <!-- Quick Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Total Studio Staff</p>
                    <p class="text-xl font-bold text-slate-900 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                    <x-icon name="users" class="w-5 h-5" />
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-2 font-medium">Telecallers, support & admin managers</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Telecallers & Support</p>
                    <p class="text-xl font-bold text-emerald-600 mt-1">{{ $stats['telecallers'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                    <x-icon name="phone-call" class="w-5 h-5" />
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-2 font-medium">Managing client follow-ups & Meta leads</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Operations & Admins</p>
                    <p class="text-xl font-bold text-violet-600 mt-1">{{ $stats['admins'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-violet-50 border border-violet-100 flex items-center justify-center text-violet-600">
                    <x-icon name="shield" class="w-5 h-5" />
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-2 font-medium">Managing shoots, finances & teams</p>
        </div>
    </div>

    <!-- Search & Filter Toolbar + Tabular View -->
    <x-card :noPadding="true">
        <form method="GET" action="{{ route('crew.index') }}" class="p-3.5 flex flex-col lg:flex-row lg:items-center justify-between gap-3 bg-slate-50/70 border-b border-slate-200/80">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-1">
                <!-- Search Input with live instant filtering -->
                <div class="relative w-full sm:max-w-xs">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <x-icon name="search" class="w-4 h-4" />
                    </div>
                    <input 
                        type="text" 
                        name="search" 
                        id="crew-search-input"
                        value="{{ request('search') }}" 
                        placeholder="Search name, phone, email, designation..." 
                        oninput="filterCrewTable(this.value)"
                        class="w-full pl-9 pr-3.5 py-1.5 text-xs bg-white rounded-lg border border-slate-200 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                    />
                </div>

                <!-- Role Filter -->
                <div class="w-full sm:w-auto">
                    <select 
                        name="role" 
                        onchange="this.form.submit()"
                        class="w-full sm:w-auto px-3 py-1.5 text-xs bg-white rounded-lg border border-slate-200 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15 font-medium text-slate-700"
                    >
                        <option value="">All Staff Roles</option>
                        <option value="telecaller" {{ request('role') === 'telecaller' ? 'selected' : '' }}>Telecaller & Support</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Studio Operations Admin</option>
                        <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                </div>

                <!-- Sort Filter -->
                <div class="w-full sm:w-auto">
                    <select 
                        name="sort" 
                        onchange="this.form.submit()"
                        class="w-full sm:w-auto px-3 py-1.5 text-xs bg-white rounded-lg border border-slate-200 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15 font-medium text-slate-700"
                    >
                        <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Sort: Newest Joined (Desc)</option>
                        <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Sort: Name (A-Z)</option>
                        <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Sort: Name (Z-A)</option>
                        <option value="leads_desc" {{ request('sort') === 'leads_desc' ? 'selected' : '' }}>Sort: Most Leads Handled</option>
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 self-end lg:self-auto">
                <x-button variant="primary" size="sm" type="submit" icon="search">
                    Filter
                </x-button>
                @if(request('search') || request('role') || request('sort'))
                    <x-button variant="secondary" size="sm" href="{{ route('crew.index') }}">
                        Clear
                    </x-button>
                @endif
            </div>
        </form>

        <!-- Responsive Tabular Data Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse" id="crew-table">
                <thead class="bg-slate-50/90 border-b border-slate-200/80 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4 first:pl-5">Team Member</th>
                        <th class="py-3 px-4">Role & Persona</th>
                        <th class="py-3 px-4">Designation / Specialization</th>
                        <th class="py-3 px-4">Contact Phone</th>
                        <th class="py-3 px-4">Assigned Leads</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Joined Date</th>
                        <th class="py-3 px-4 text-right pr-5">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700" id="crew-table-body">
                    @forelse($crewMembers as $member)
                        <tr class="hover:bg-slate-50/80 transition-colors crew-row" data-search="{{ strtolower($member->name . ' ' . $member->email . ' ' . $member->phone . ' ' . $member->role . ' ' . $member->specialization) }}">
                            <!-- Member Name & Avatar -->
                            <td class="py-3.5 px-4 first:pl-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl font-bold flex items-center justify-center text-xs shrink-0 {{ $member->role === 'telecaller' ? 'bg-emerald-50 border border-emerald-100 text-emerald-700' : ($member->role === 'super_admin' ? 'bg-amber-50 border border-amber-100 text-amber-700' : 'bg-indigo-50 border border-indigo-100 text-indigo-700') }}">
                                        {{ substr($member->name, 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-900 tracking-tight truncate">{{ $member->name }}</p>
                                        <p class="text-[11px] text-slate-400 truncate">{{ $member->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Role Badge -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($member->role === 'telecaller')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Telecaller & Support
                                    </span>
                                @elseif($member->role === 'super_admin')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Super Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                        Studio Admin
                                    </span>
                                @endif
                            </td>

                            <!-- Designation -->
                            <td class="py-3.5 px-4">
                                <span class="font-medium text-slate-700">
                                    {{ $member->specialization ?: ($member->role === 'telecaller' ? 'Client Support Specialist' : 'Operations Manager') }}
                                </span>
                            </td>

                            <!-- Phone & WhatsApp -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-slate-800 font-medium">{{ $member->phone }}</span>
                                    @if($member->phone)
                                        @php
                                            $cleanPhone = preg_replace('/[^0-9]/', '', $member->phone);
                                            if (strlen($cleanPhone) === 10) $cleanPhone = '91' . $cleanPhone;
                                        @endphp
                                        <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" title="WhatsApp Message" class="text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 p-1 rounded transition-colors inline-flex items-center">
                                            <x-icon name="message-circle" class="w-3.5 h-3.5" />
                                        </a>
                                    @endif
                                </div>
                            </td>

                            <!-- Assigned Leads -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($member->role === 'telecaller')
                                    <a href="{{ route('leads.index') }}?search={{ urlencode($member->name) }}" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors" title="View leads assigned to {{ $member->name }}">
                                        <span>{{ $member->assigned_leads_count }} Leads</span>
                                        <x-icon name="arrow-up-right" class="w-3 h-3" />
                                    </a>
                                @else
                                    <span class="text-slate-400 text-[11px]">All Leads Access</span>
                                @endif
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
                                {{ $member->created_at?->format('d M Y') ?? 'Recently' }}
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right pr-5 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <x-whatsapp-button :phone="$member->phone" />

                                    @if($member->id !== auth()->id() && $member->role !== 'super_admin')
                                        <form method="POST" action="{{ route('crew.destroy', $member) }}" onsubmit="return confirm('Remove crew member {{ $member->name }} from studio staff?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Remove Member" class="p-1 text-slate-400 hover:text-rose-600 rounded-md hover:bg-rose-50 transition-colors">
                                                <x-icon name="trash-2" class="w-3.5 h-3.5" />
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[10px] text-slate-400 font-medium px-2 py-0.5 rounded bg-slate-100">Current</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="server-empty-row">
                            <td colspan="8" class="py-12 text-center">
                                <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-2">
                                    <x-icon name="users" class="w-5 h-5" />
                                </div>
                                <h3 class="text-xs font-bold text-slate-800">No Crew Members Found</h3>
                                <p class="text-[11px] text-slate-400 mt-0.5">Try adjusting your search terms or filters.</p>
                                <div class="mt-3">
                                    <x-button variant="primary" size="xs" icon="plus" onclick="document.getElementById('modal-add-crew').classList.remove('hidden')">
                                        Add Crew Member
                                    </x-button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    <tr id="client-no-match-row" class="hidden">
                        <td colspan="8" class="py-10 text-center">
                            <p class="text-xs font-bold text-slate-800">No matching crew members on this page</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Check spelling or clear the search input.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer Summary -->
        <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
            <div>
                Showing <span class="font-bold text-slate-800" id="visible-crew-count">{{ $crewMembers->count() }}</span> of <span class="font-bold text-slate-800">{{ $crewMembers->total() }}</span> studio crew & support staff members
            </div>
            <div class="flex items-center gap-4 text-[11px] text-slate-400">
                <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> {{ $stats['telecallers'] }} Telecallers</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-indigo-500"></span> {{ $stats['admins'] }} Admins</span>
            </div>
        </div>

        @if($crewMembers->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">
                {{ $crewMembers->links() }}
            </div>
        @endif
    </x-card>
</div>

<!-- Modal: Add Crew Member -->
<x-modal id="modal-add-crew" title="Add New Crew & Staff Member" subtitle="Create an account for telecallers, customer support, or studio managers" maxWidth="md">
    <form method="POST" action="{{ route('crew.store') }}" class="space-y-4">
        @csrf

        @if($errors->any())
            <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-700 space-y-1">
                @foreach($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Full Name <span class="text-rose-500">*</span></label>
            <input 
                type="text" 
                name="name" 
                required 
                placeholder="e.g. Ananya Patel"
                value="{{ old('name') }}"
                class="w-full px-3 py-2 text-xs bg-white rounded-lg border border-slate-200 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
            />
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Staff Role <span class="text-rose-500">*</span></label>
            <select 
                name="role" 
                required
                class="w-full px-3 py-2 text-xs bg-white rounded-lg border border-slate-200 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
            >
                <option value="telecaller" {{ old('role') === 'telecaller' ? 'selected' : '' }}>Telecaller & Client Support (Handles inquiries, calls & leads)</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Studio Operations Admin (Full management access)</option>
            </select>
            <p class="text-[11px] text-slate-400 mt-1">Telecallers can manage incoming leads, call logs, and customer communications.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address <span class="text-rose-500">*</span></label>
                <input 
                    type="email" 
                    name="email" 
                    required 
                    placeholder="staff@studio.com"
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
            <label class="block text-xs font-semibold text-slate-700 mb-1">Designation / Responsibility</label>
            <input 
                type="text" 
                name="specialization" 
                placeholder="e.g. Senior Telecaller, Client Relations, Shoot Coordinator"
                value="{{ old('specialization') }}"
                class="w-full px-3 py-2 text-xs bg-white rounded-lg border border-slate-200 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
            />
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
            <x-button variant="secondary" size="sm" type="button" onclick="document.getElementById('modal-add-crew').classList.add('hidden')">
                Cancel
            </x-button>
            <x-button variant="primary" size="sm" type="submit" icon="user-plus">
                Save Crew Member
            </x-button>
        </div>
    </form>
</x-modal>

<script>
    function filterCrewTable(query) {
        const term = query.toLowerCase().trim();
        const rows = document.querySelectorAll('.crew-row');
        const noMatchRow = document.getElementById('client-no-match-row');
        const countEl = document.getElementById('visible-crew-count');
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
