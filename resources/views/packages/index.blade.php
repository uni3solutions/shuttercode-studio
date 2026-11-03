@extends('layouts.app', [
    'title' => 'Photography Packages',
    'breadcrumbs' => ['Home' => '/', 'Configuration' => '#', 'Packages' => route('packages.index')]
])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Telecaller Packages & Pricing</h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    {{ $totalCount }} {{ Str::plural('Package', $totalCount) }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Manage telecaller service offerings, crew allocations, deliverables, and advance deposits</p>
        </div>
        <div class="flex items-center gap-2.5">
            <x-button variant="primary" size="sm" icon="plus" href="{{ route('packages.create') }}">
                <span>Create New Package</span>
            </x-button>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>Total Packages</span>
                <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <x-icon name="layers" class="w-3.5 h-3.5" />
                </div>
            </div>
            <div class="text-2xl font-bold text-slate-900 mt-2">{{ $totalCount }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1.5">
                <span class="text-emerald-600 font-semibold">{{ $activeCount }} Active</span>
                <span>&bull;</span>
                <span>{{ $totalCount - $activeCount }} Inactive</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>My Telecaller Packages</span>
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <x-icon name="user" class="w-3.5 h-3.5" />
                </div>
            </div>
            <div class="text-2xl font-bold text-slate-900 mt-2">{{ $myPackagesCount }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">
                Assigned to {{ auth()->user()->name }}
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>{{ auth()->user()->isTelecaller() ? 'Active Packages' : 'Active Telecallers' }}</span>
                <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                    <x-icon name="{{ auth()->user()->isTelecaller() ? 'check-circle' : 'users' }}" class="w-3.5 h-3.5" />
                </div>
            </div>
            <div class="text-2xl font-bold text-slate-900 mt-2">{{ auth()->user()->isTelecaller() ? $activeCount : $telecallers->count() }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">
                {{ auth()->user()->isTelecaller() ? 'Ready for client quotes' : 'Managing custom quotes' }}
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>Shoots Booked</span>
                <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <x-icon name="calendar" class="w-3.5 h-3.5" />
                </div>
            </div>
            <div class="text-2xl font-bold text-slate-900 mt-2">{{ $totalShootsBooked }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">
                Converted client bookings
            </div>
        </div>
    </div>

    <!-- Filter and Search Bar -->
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-4">
        <form method="GET" action="{{ route('packages.index') }}" class="space-y-3">
            <!-- Retain current view preference if set -->
            @if(request('view'))
                <input type="hidden" name="view" value="{{ request('view') }}">
            @endif

            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                <!-- Search Input -->
                <div class="{{ auth()->user()->isTelecaller() ? 'md:col-span-7' : 'md:col-span-4' }} relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <x-icon name="search" class="w-4 h-4" />
                    </div>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Search package name, deliverables..."
                        class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border border-slate-300 bg-white placeholder-slate-400 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                    />
                    @if(request('search'))
                        <a href="{{ route('packages.index', request()->except('search')) }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600">
                            <x-icon name="x" class="w-3.5 h-3.5" />
                        </a>
                    @endif
                </div>

                <!-- Telecaller Filter -->
                @if(!auth()->user()->isTelecaller())
                <div class="md:col-span-3">
                    <select 
                        name="telecaller_id" 
                        onchange="this.form.submit()"
                        class="w-full py-2 px-3 text-xs rounded-lg border border-slate-300 bg-white text-slate-700 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                    >
                        <option value="all">All Telecallers / Creators</option>
                        <option value="me" @selected(request('telecaller_id') === 'me' || request('scope') === 'my')>
                            👤 My Packages ({{ auth()->user()->name }})
                        </option>
                        @foreach($telecallers as $tc)
                            @if($tc->id !== auth()->id())
                                <option value="{{ $tc->id }}" @selected(request('telecaller_id') == $tc->id)>
                                    {{ $tc->name }} ({{ ucfirst($tc->role) }})
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Category Filter -->
                <div class="md:col-span-2">
                    <select 
                        name="category" 
                        onchange="this.form.submit()"
                        class="w-full py-2 px-3 text-xs rounded-lg border border-slate-300 bg-white text-slate-700 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                    >
                        <option value="all">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="md:col-span-2">
                    <select 
                        name="status" 
                        onchange="this.form.submit()"
                        class="w-full py-2 px-3 text-xs rounded-lg border border-slate-300 bg-white text-slate-700 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                    >
                        <option value="all" @selected(request('status') === 'all')>All Status</option>
                        <option value="active" @selected(request('status') === 'active')>Active Only</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive Only</option>
                    </select>
                </div>

                <!-- Submit / Actions -->
                <div class="md:col-span-1 flex items-center justify-end gap-1.5">
                    <button type="submit" class="w-full py-2 px-3 bg-[#4F46E5] text-white text-xs font-semibold rounded-lg hover:bg-[#4338CA] transition cursor-pointer">
                        Filter
                    </button>
                </div>
            </div>

            <!-- Active Filter Badges & View Switcher -->
            <div class="flex flex-wrap items-center justify-between pt-2 border-t border-slate-100 text-xs">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-slate-400 font-medium">Quick Filters:</span>
                    <a 
                        href="{{ route('packages.index') }}" 
                        class="px-2.5 py-1 rounded-md text-[11px] font-medium transition {{ !request()->hasAny(['telecaller_id', 'scope', 'category', 'status', 'search']) ? 'bg-indigo-50 text-indigo-700 font-semibold border border-indigo-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                    >
                        All ({{ $totalCount }})
                    </a>
                    <a 
                        href="{{ route('packages.index', ['scope' => 'my']) }}" 
                        class="px-2.5 py-1 rounded-md text-[11px] font-medium transition {{ request('scope') === 'my' || request('telecaller_id') === 'me' ? 'bg-indigo-50 text-indigo-700 font-semibold border border-indigo-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                    >
                        My Packages ({{ $myPackagesCount }})
                    </a>
                    <a 
                        href="{{ route('packages.index', ['status' => 'active']) }}" 
                        class="px-2.5 py-1 rounded-md text-[11px] font-medium transition {{ request('status') === 'active' ? 'bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                    >
                        Active ({{ $activeCount }})
                    </a>

                    @if(request()->hasAny(['search', 'telecaller_id', 'scope', 'category', 'status']))
                        <a href="{{ route('packages.index') }}" class="text-[11px] text-rose-600 hover:text-rose-700 font-semibold ml-2 underline">
                            Clear all filters
                        </a>
                    @endif
                </div>

                <!-- View Mode Toggle (Table / Card) -->
                @php $currentView = request('view', 'table'); @endphp
                <div class="flex items-center gap-1 bg-slate-100 p-0.5 rounded-lg border border-slate-200/80">
                    <a 
                        href="{{ request()->fullUrlWithQuery(['view' => 'table']) }}" 
                        class="px-2.5 py-1 rounded-md text-[11px] font-medium flex items-center gap-1.5 transition {{ $currentView === 'table' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-500 hover:text-slate-900' }}"
                        title="Table View"
                    >
                        <x-icon name="list" class="w-3.5 h-3.5" />
                        <span>Table</span>
                    </a>
                    <a 
                        href="{{ request()->fullUrlWithQuery(['view' => 'card']) }}" 
                        class="px-2.5 py-1 rounded-md text-[11px] font-medium flex items-center gap-1.5 transition {{ $currentView === 'card' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-500 hover:text-slate-900' }}"
                        title="Card View"
                    >
                        <x-icon name="grid" class="w-3.5 h-3.5" />
                        <span>Cards</span>
                    </a>
                </div>
            </div>
        </form>
    </div>

    @if($packages->isEmpty())
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-12 text-center">
            <div class="w-12 h-12 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                <x-icon name="inbox" class="w-6 h-6" />
            </div>
            <h3 class="text-base font-bold text-slate-900">No photography packages found</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                No packages matched your filter criteria. Try clearing the search or create a new telecaller package.
            </p>
            <div class="mt-4 flex items-center justify-center gap-2">
                <a href="{{ route('packages.index') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200">
                    Reset Filters
                </a>
                <x-button variant="primary" size="sm" icon="plus" href="{{ route('packages.create') }}">
                    Create Package
                </x-button>
            </div>
        </div>
    @else
        @if($currentView === 'table')
            <!-- TABLE FORMAT -->
            <div class="overflow-x-auto rounded-xl border border-slate-200/90 bg-white shadow-2xs">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-4 py-3.5 pl-5">Package &amp; Deliverables</th>
                            <th scope="col" class="px-4 py-3.5">Telecaller / Owner</th>
                            <th scope="col" class="px-4 py-3.5">Crew Allocation</th>
                            <th scope="col" class="px-4 py-3.5">Pricing &amp; Advance</th>
                            <th scope="col" class="px-4 py-3.5 text-center">Shoots</th>
                            <th scope="col" class="px-4 py-3.5 text-center">Status</th>
                            <th scope="col" class="px-4 py-3.5 pr-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($packages as $pkg)
                            <tr class="hover:bg-slate-50/70 transition-colors {{ !$pkg->is_active ? 'opacity-65 bg-slate-50/30' : '' }}">
                                <!-- 1. Package & Deliverables -->
                                <td class="px-4 py-3.5 pl-5 max-w-xs sm:max-w-sm">
                                    <div class="flex items-start gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center shrink-0 mt-0.5 font-bold text-xs">
                                            {{ strtoupper(substr($pkg->name, 0, 1)) }}
                                        </div>
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <a href="{{ route('packages.edit', $pkg) }}" class="font-bold text-slate-900 hover:text-[#4F46E5] transition text-sm">
                                                    {{ $pkg->name }}
                                                </a>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                                    {{ $pkg->category }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 line-clamp-1">
                                                {{ $pkg->description ?: 'Standard studio photography coverage.' }}
                                            </p>
                                            @if($pkg->deliverables)
                                                <div class="text-[10px] text-slate-400 line-clamp-1 italic">
                                                    {{ Str::limit(str_replace("\n", ' • ', $pkg->deliverables), 70) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- 2. Telecaller Name -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @php
                                        $telecaller = $pkg->creator ?? \App\Models\User::where('role', 'telecaller')->first() ?? auth()->user();
                                    @endphp
                                    @if($telecaller)
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 text-white font-bold text-[10px] flex items-center justify-center shadow-2xs shrink-0">
                                                {{ strtoupper(substr($telecaller->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-bold text-slate-900 text-xs">{{ $telecaller->name }}</span>
                                                    @if($telecaller->id === auth()->id())
                                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-indigo-100 text-indigo-700 border border-indigo-200">
                                                            Created by You
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-[10px] text-slate-500 flex items-center gap-1">
                                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                                    <span>{{ $telecaller->role === 'telecaller' ? 'Telecaller' : ucfirst($telecaller->role) }}</span>
                                                    @if($telecaller->phone)
                                                        <span>&bull; {{ $telecaller->phone }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-1.5 text-slate-400">
                                            <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-[10px]">
                                                <x-icon name="user" class="w-3 h-3" />
                                            </div>
                                            <span class="text-[11px] font-medium">Unassigned</span>
                                        </div>
                                    @endif
                                </td>

                                <!-- 3. Crew Allocation -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <div class="font-semibold text-slate-800 text-[11px]">
                                            {{ $pkg->photographers_count }} Photo @if($pkg->videographers_count > 0) + {{ $pkg->videographers_count }} Video @endif
                                        </div>
                                        <div class="flex items-center gap-2 text-[10px] text-slate-500">
                                            <span>{{ $pkg->duration_hours }}h Coverage</span>
                                            <span>&bull;</span>
                                            @if($pkg->drone_included)
                                                <span class="text-indigo-600 font-semibold flex items-center gap-0.5">
                                                    <span class="w-1 h-1 rounded-full bg-indigo-500"></span> Drone 4K
                                                </span>
                                            @else
                                                <span class="text-slate-400">No Drone</span>
                                            @endif
                                            <span>&bull;</span>
                                            <span>{{ $pkg->album_count }} {{ Str::plural('Album', $pkg->album_count) }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- 4. Pricing & Advance -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-bold text-slate-900">
                                            ₹{{ number_format($pkg->price) }}
                                        </div>
                                        <div class="text-[10px] text-indigo-600 font-medium mt-0.5">
                                            Advance: ₹{{ number_format($pkg->advance_amount) }}
                                        </div>
                                        <div class="text-[9px] text-slate-400">
                                            Bal: ₹{{ number_format(max(0, $pkg->price - $pkg->advance_amount)) }}
                                        </div>
                                    </div>
                                </td>

                                <!-- 5. Bookings -->
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $pkg->shoots_count > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-50 text-slate-500' }}">
                                        {{ $pkg->shoots_count }} {{ Str::plural('shoot', $pkg->shoots_count) }}
                                    </span>
                                </td>

                                <!-- 6. Status -->
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    @if($pkg->is_active)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <!-- 7. Actions -->
                                <td class="px-4 py-3.5 pr-5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <form action="{{ route('packages.toggle', $pkg) }}" method="POST">
                                            @csrf
                                            <button 
                                                type="submit" 
                                                class="px-2 py-1 text-[11px] font-medium rounded border {{ $pkg->is_active ? 'border-slate-200 text-slate-600 hover:bg-slate-100' : 'border-emerald-200 text-emerald-700 bg-emerald-50 hover:bg-emerald-100' }} transition cursor-pointer"
                                            >
                                                {{ $pkg->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                        <a 
                                            href="{{ route('packages.edit', $pkg) }}" 
                                            class="p-1 text-slate-400 hover:text-indigo-600 transition rounded hover:bg-indigo-50"
                                            title="Edit Package"
                                        >
                                            <x-icon name="pencil" class="w-4 h-4" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <!-- CARD FORMAT (WITH PROMINENT TELECALLER DETAILS) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($packages as $pkg)
                    <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs hover:border-slate-300 transition-all flex flex-col justify-between overflow-hidden {{ !$pkg->is_active ? 'opacity-65 bg-slate-50/50' : '' }}">
                        <div class="p-5">
                            <!-- Category and Telecaller Attribution Header -->
                            <div class="flex items-center justify-between mb-3 pb-3 border-b border-slate-100">
                                <div class="flex items-center gap-2">
                                    @php
                                        $telecaller = $pkg->creator ?? \App\Models\User::where('role', 'telecaller')->first() ?? auth()->user();
                                    @endphp
                                    @if($telecaller)
                                        <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 text-white font-bold text-[10px] flex items-center justify-center shrink-0 shadow-2xs">
                                            {{ strtoupper(substr($telecaller->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-1">
                                                <span class="text-xs font-bold text-slate-900">{{ $telecaller->name }}</span>
                                                @if($telecaller->id === auth()->id())
                                                    <span class="text-[9px] font-semibold bg-indigo-50 text-indigo-700 px-1 py-0.2 rounded border border-indigo-200">
                                                        You
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-[10px] text-slate-400">Telecaller Package</div>
                                        </div>
                                    @else
                                        <div class="text-xs font-semibold text-slate-400">Unassigned Telecaller</div>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <x-badge variant="info" size="xs">{{ $pkg->category }}</x-badge>
                                    @if($pkg->is_active)
                                        <span class="w-2 h-2 rounded-full bg-emerald-500" title="Active"></span>
                                    @else
                                        <span class="w-2 h-2 rounded-full bg-slate-300" title="Inactive"></span>
                                    @endif
                                </div>
                            </div>

                            <h3 class="text-base font-bold text-slate-900 tracking-tight">{{ $pkg->name }}</h3>
                            <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $pkg->description }}</p>

                            <div class="my-4 pt-3 border-t border-slate-100 flex items-baseline justify-between">
                                <div>
                                    <span class="text-xs text-slate-400">Package Price</span>
                                    <div class="text-xl font-bold text-slate-900">₹{{ number_format($pkg->price) }}</div>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-slate-400">Advance Required</span>
                                    <div class="text-sm font-semibold text-indigo-600">₹{{ number_format($pkg->advance_amount) }}</div>
                                </div>
                            </div>

                            <!-- Crew & Deliverables Specs -->
                            <div class="space-y-1.5 text-xs text-slate-600 bg-slate-50 p-3 rounded-lg border border-slate-100">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Crew Allocation:</span>
                                    <span class="font-medium text-slate-800">
                                        {{ $pkg->photographers_count }} Photo @if($pkg->videographers_count > 0) + {{ $pkg->videographers_count }} Video @endif
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Drone Coverage:</span>
                                    <span class="font-medium {{ $pkg->drone_included ? 'text-indigo-600' : 'text-slate-400' }}">
                                        {{ $pkg->drone_included ? 'Included (4K)' : 'Not Included' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Photo Albums:</span>
                                    <span class="font-medium text-slate-800">{{ $pkg->album_count }} Printed Albums</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Coverage Duration:</span>
                                    <span class="font-medium text-slate-800">{{ $pkg->duration_hours }} Hours</span>
                                </div>
                            </div>

                            @if($pkg->deliverables)
                                <div class="mt-3">
                                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Inclusions:</p>
                                    <pre class="text-[11px] text-slate-600 font-sans whitespace-pre-line line-clamp-3">{{ $pkg->deliverables }}</pre>
                                </div>
                            @endif
                        </div>

                        <div class="px-5 py-3 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="text-slate-400">{{ $pkg->shoots_count }} shoots booked</span>
                            <div class="flex items-center gap-2">
                                <form action="{{ route('packages.toggle', $pkg) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs text-slate-500 hover:text-slate-800 cursor-pointer">
                                        {{ $pkg->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <span class="text-slate-300">&bull;</span>
                                <a href="{{ route('packages.edit', $pkg) }}" class="text-[#4F46E5] font-semibold hover:underline">
                                    Edit Package
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Pagination Bar -->
        <div class="p-4 bg-white rounded-xl border border-slate-200/90 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div>
                Showing <span class="font-semibold text-slate-800">{{ $packages->firstItem() ?? 0 }}</span> to <span class="font-semibold text-slate-800">{{ $packages->lastItem() ?? 0 }}</span> of <span class="font-semibold text-slate-800">{{ $packages->total() }}</span> packages
            </div>
            @if($packages->hasPages())
                <div class="scale-90 origin-right">
                    {{ $packages->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
