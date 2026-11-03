@props([
    'title' => 'Dashboard',
    'breadcrumbs' => []
])

@php
    $currentUser = auth()->user();
    $currentRole = $currentUser?->role ?? 'super_admin';
    $roleLabels = [
        'super_admin' => 'Super Admin',
        'admin' => 'Studio Admin',
        'telecaller' => 'Telecaller',
        'photographer' => 'Photographer',
    ];

    // Live studio notifications
    $today = now()->toDateString();
    $alertLeadsQuery = \App\Models\Lead::where('status', 'new');
    if ($currentUser?->isTelecaller()) {
        $alertLeadsQuery->where(function ($q) use ($currentUser) {
            $q->where('assigned_to', $currentUser->id)
                ->orWhere('created_by', $currentUser->id);
        });
    }
    $alertLeads = $alertLeadsQuery->latest()->take(3)->get();

    $alertShootsQuery = \App\Models\Shoot::whereDate('shoot_date', $today)->with('customer');
    if ($currentUser?->isTelecaller()) {
        $alertShootsQuery->where('created_by', $currentUser->id);
    }
    $alertShoots = $alertShootsQuery->take(3)->get();

    $alertPayments = \App\Models\Payment::where('status', 'verified')->latest('paid_at')->with('customer')->take(2)->get();
    $recentActivities = \App\Models\ActivityLog::latest('created_at')->take(3)->get();

    $notificationCount = $alertLeads->count() + $alertShoots->count();
@endphp

<header class="sticky top-0 z-30 h-16 bg-white border-b border-slate-200/90 flex items-center justify-between px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-3">
        <!-- Mobile Menu Button -->
        <button type="button" class="lg:hidden p-2 text-slate-500 hover:text-slate-800 rounded-lg hover:bg-slate-100" onclick="toggleSidebar()">
            <x-icon name="menu" class="w-5 h-5" />
        </button>

        <!-- Page Title & Breadcrumbs -->
        <div>
            @if(!empty($breadcrumbs))
                <nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-0.5">
                    @foreach($breadcrumbs as $label => $url)
                        @if(!$loop->last)
                            <a href="{{ $url }}" class="hover:text-slate-600 transition-colors">{{ $label }}</a>
                            <x-icon name="chevron-right" class="w-3 h-3 text-slate-300" />
                        @else
                            <span class="text-slate-600 font-medium">{{ $label }}</span>
                        @endif
                    @endforeach
                </nav>
            @endif
            <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight leading-tight">{{ $title }}</h1>
        </div>
    </div>

    <!-- Header Actions & Search -->
    <div class="flex items-center gap-2 sm:gap-3.5">
        <!-- Fast Role Switcher Dropdown for Pairing/Testing -->
        <div class="relative" id="role-menu-container">
            <button 
                type="button" 
                onclick="toggleDropdown('role-dropdown')" 
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-indigo-50/80 hover:bg-indigo-100/80 text-indigo-700 text-xs font-semibold border border-indigo-200/60 transition-colors cursor-pointer"
                title="Switch Role View"
            >
                <x-icon name="shield" class="w-3.5 h-3.5 text-indigo-600" />
                <span class="hidden md:inline">Role:</span>
                <span>{{ $roleLabels[$currentRole] ?? 'Admin' }}</span>
                <x-icon name="chevron-down" class="w-3 h-3 text-indigo-500" />
            </button>

            <div id="role-dropdown" class="hidden absolute right-0 mt-2 w-48 rounded-xl bg-white border border-slate-200 shadow-lg py-1.5 z-50 text-xs">
                <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                    Switch Active Persona
                </div>
                <a href="{{ route('switch.role', 'super_admin') }}" class="flex items-center justify-between px-3 py-2 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 font-medium">
                    <span>Super Admin</span>
                    @if($currentRole === 'super_admin') <x-icon name="check" class="w-3.5 h-3.5 text-indigo-600" /> @endif
                </a>
                <a href="{{ route('switch.role', 'admin') }}" class="flex items-center justify-between px-3 py-2 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 font-medium">
                    <span>Studio Admin</span>
                    @if($currentRole === 'admin') <x-icon name="check" class="w-3.5 h-3.5 text-indigo-600" /> @endif
                </a>
                <a href="{{ route('switch.role', 'telecaller') }}" class="flex items-center justify-between px-3 py-2 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 font-medium">
                    <span>Telecaller</span>
                    @if($currentRole === 'telecaller') <x-icon name="check" class="w-3.5 h-3.5 text-indigo-600" /> @endif
                </a>
                <a href="{{ route('switch.role', 'photographer') }}" class="flex items-center justify-between px-3 py-2 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 font-medium">
                    <span>Photographer</span>
                    @if($currentRole === 'photographer') <x-icon name="check" class="w-3.5 h-3.5 text-indigo-600" /> @endif
                </a>
            </div>
        </div>

        @unless($currentUser?->isPhotographer())
            <!-- Global Spotlight Search Button -->
            <button 
                type="button" 
                onclick="openSpotlightSearch()" 
                class="relative hidden lg:flex items-center justify-between w-52 pl-3 pr-2 py-1.5 text-xs rounded-lg bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-400 hover:text-slate-600 hover:border-slate-300 transition-all cursor-pointer group"
                title="Search shoots, clients, leads, staff (⌘K / Ctrl+K)"
            >
                <div class="flex items-center gap-2">
                    <x-icon name="search" class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-600" />
                    <span class="text-xs text-slate-400 group-hover:text-slate-600">Search studio...</span>
                </div>
                <kbd class="text-[10px] font-mono text-slate-400 group-hover:text-indigo-600 border border-slate-200 bg-white rounded px-1.5 py-0.5 shadow-2xs font-semibold">⌘K</kbd>
            </button>

            <!-- Mobile Search Icon Button -->
            <button 
                type="button" 
                onclick="openSpotlightSearch()" 
                class="lg:hidden p-2 text-slate-500 hover:text-slate-700 rounded-lg hover:bg-slate-100 transition-colors"
                title="Search"
            >
                <x-icon name="search" class="w-4 h-4" />
            </button>

            <!-- Notifications Center -->
            <div class="relative" id="notifications-dropdown-container">
                <button 
                    type="button" 
                    onclick="toggleDropdown('notifications-dropdown')" 
                    class="relative p-2 text-slate-500 hover:text-slate-700 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer"
                    title="Studio Notifications"
                >
                    <x-icon name="bell" class="w-4 h-4" />
                    @if($notificationCount > 0)
                        <span id="notification-badge" class="absolute top-1 right-1 flex items-center justify-center min-w-4 h-4 px-1 text-[9px] font-bold text-white bg-[#4F46E5] rounded-full ring-2 ring-white">
                            {{ $notificationCount }}
                        </span>
                    @else
                        <span id="notification-badge" class="hidden absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-[#4F46E5] ring-2 ring-white"></span>
                    @endif
                </button>

                <!-- Notification Dropdown -->
                <div id="notifications-dropdown" class="hidden absolute right-0 mt-2 w-84 sm:w-96 rounded-2xl bg-white border border-slate-200 shadow-xl py-2 z-50">
                    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-900">Studio Notifications</span>
                            @if($notificationCount > 0)
                                <span class="text-[10px] bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full font-bold">{{ $notificationCount }} New</span>
                            @endif
                        </div>
                        <button 
                            type="button" 
                            onclick="clearNotifications()" 
                            class="text-[11px] text-[#4F46E5] hover:text-[#4338CA] font-medium hover:underline cursor-pointer"
                        >
                            Mark all as read
                        </button>
                    </div>

                    <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto">
                        <!-- 1. Active Shoots Today -->
                        @foreach($alertShoots as $shoot)
                            <a href="{{ route('shoots.show', $shoot) }}" class="p-3.5 hover:bg-slate-50 flex gap-3 transition-colors block group">
                                <div class="w-8 h-8 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <x-icon name="camera" class="w-4 h-4" />
                                </div>
                                <div class="text-xs flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="font-bold text-slate-900 truncate">{{ $shoot->title ?: "Shoot #{$shoot->shoot_number}" }}</p>
                                        <span class="text-[10px] font-semibold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded">Live Today</span>
                                    </div>
                                    <p class="text-slate-500 text-[11px] mt-0.5 truncate">{{ $shoot->customer?->name }} • {{ $shoot->venue ?: 'Venue Scheduled' }}</p>
                                    <span class="text-[10px] text-slate-400 mt-1 block font-mono">{{ $shoot->shoot_number }}</span>
                                </div>
                            </a>
                        @endforeach

                        <!-- 2. Uncontacted Incoming Leads -->
                        @foreach($alertLeads as $lead)
                            <a href="{{ route('leads.show', $lead) }}" class="p-3.5 hover:bg-slate-50 flex gap-3 transition-colors block group">
                                <div class="w-8 h-8 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <x-icon name="message-square-quote" class="w-4 h-4" />
                                </div>
                                <div class="text-xs flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="font-bold text-slate-900 truncate">New Lead: {{ $lead->customer_name }}</p>
                                        <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded">Action Needed</span>
                                    </div>
                                    <p class="text-slate-500 text-[11px] mt-0.5 truncate">{{ $lead->event_type }} • {{ $lead->phone }}</p>
                                    <span class="text-[10px] text-slate-400 mt-1 block">{{ $lead->created_at?->diffForHumans() ?? 'Recently' }}</span>
                                </div>
                            </a>
                        @endforeach

                        <!-- 3. Recent Verified Payments -->
                        @foreach($alertPayments as $payment)
                            <a href="{{ route('payments.index') }}" class="p-3.5 hover:bg-slate-50 flex gap-3 transition-colors block group">
                                <div class="w-8 h-8 rounded-xl bg-violet-50 border border-violet-100 text-violet-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <x-icon name="credit-card" class="w-4 h-4" />
                                </div>
                                <div class="text-xs flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="font-bold text-slate-900 truncate">Payment Verified: ₹{{ number_format($payment->amount) }}</p>
                                        <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded">Success</span>
                                    </div>
                                    <p class="text-slate-500 text-[11px] mt-0.5 truncate">Client: {{ $payment->customer?->name }} • {{ ucfirst($payment->payment_method) }}</p>
                                    <span class="text-[10px] text-slate-400 mt-1 block">{{ $payment->paid_at?->diffForHumans() ?? 'Today' }}</span>
                                </div>
                            </a>
                        @endforeach

                        @if($alertShoots->isEmpty() && $alertLeads->isEmpty() && $alertPayments->isEmpty())
                            <div class="py-8 px-4 text-center">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2">
                                    <x-icon name="check-circle" class="w-5 h-5" />
                                </div>
                                <p class="text-xs font-bold text-slate-800">All Caught Up!</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">No urgent notifications or pending lead actions.</p>
                            </div>
                        @endif
                    </div>

                    <div class="px-4 py-2.5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between text-xs">
                        <a href="{{ route('leads.index') }}" class="text-slate-600 hover:text-slate-900 font-medium">View Leads</a>
                        <a href="{{ route('shoots.index') }}" class="text-[#4F46E5] hover:text-[#4338CA] font-bold">All Shoots &rarr;</a>
                    </div>
                </div>
            </div>

            <div class="h-5 w-px bg-slate-200"></div>

            <!-- Quick Primary Action -->
            <x-button variant="primary" size="sm" icon="plus" href="{{ route('shoots.create') }}">
                <span class="hidden sm:inline">New Booking</span>
                <span class="sm:hidden">New</span>
            </x-button>
        @endunless

        <a href="{{ route('logout') }}" title="Sign Out" class="p-2 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors">
            <x-icon name="log-out" class="w-4 h-4" />
        </a>
    </div>
</header>

<!-- Global Studio Spotlight Search Modal (⌘K / Ctrl+K) -->
<div id="spotlight-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" onclick="closeSpotlightSearch()"></div>

    <div class="flex min-h-full items-start justify-center p-4 pt-16 sm:p-6 sm:pt-24 text-center">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full max-w-2xl border border-slate-200">
            <!-- Search Input Header -->
            <div class="p-4 border-b border-slate-100 flex items-center gap-3">
                <div class="text-slate-400">
                    <x-icon name="search" class="w-5 h-5 text-indigo-600" />
                </div>
                <input 
                    type="text" 
                    id="spotlight-input" 
                    placeholder="Search shoots, customers, leads, photographers, phone numbers..." 
                    class="w-full text-sm text-slate-900 placeholder-slate-400 bg-transparent border-0 focus:outline-hidden focus:ring-0 p-0"
                    autocomplete="off"
                    oninput="handleSpotlightInput(this.value)"
                    onkeydown="handleSpotlightKeydown(event)"
                />
                <button type="button" class="text-xs text-slate-400 hover:text-slate-600 px-2 py-1 rounded bg-slate-100 font-mono" onclick="closeSpotlightSearch()">
                    ESC
                </button>
            </div>

            <!-- Search Content Area -->
            <div id="spotlight-content" class="max-h-96 overflow-y-auto p-3">
                <!-- Initial Quick Shortcuts (when empty) -->
                <div id="spotlight-shortcuts" class="space-y-4 p-2">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Quick Navigation</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <a href="{{ route('shoots.index') }}" class="p-2.5 rounded-xl border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/50 flex items-center gap-3 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <x-icon name="camera" class="w-4 h-4" />
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900">Shoots & Bookings</p>
                                <p class="text-[11px] text-slate-400">Schedule & manage shoots</p>
                            </div>
                        </a>

                        <a href="{{ route('customers.index') }}" class="p-2.5 rounded-xl border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/50 flex items-center gap-3 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <x-icon name="users" class="w-4 h-4" />
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900">Customer Directory</p>
                                <p class="text-[11px] text-slate-400">Clients, profiles & history</p>
                            </div>
                        </a>

                        <a href="{{ route('leads.index') }}" class="p-2.5 rounded-xl border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/50 flex items-center gap-3 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <x-icon name="message-square-quote" class="w-4 h-4" />
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900">Leads & Enquiries</p>
                                <p class="text-[11px] text-slate-400">WhatsApp & Meta leads</p>
                            </div>
                        </a>

                        <a href="{{ route('photographers.index') }}" class="p-2.5 rounded-xl border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/50 flex items-center gap-3 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <x-icon name="camera" class="w-4 h-4" />
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900">Photographers</p>
                                <p class="text-[11px] text-slate-400">Artists & creative crew</p>
                            </div>
                        </a>

                        <a href="{{ route('crew.index') }}" class="p-2.5 rounded-xl border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/50 flex items-center gap-3 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <x-icon name="user-check" class="w-4 h-4" />
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900">Crew & Staff</p>
                                <p class="text-[11px] text-slate-400">Telecallers & operations</p>
                            </div>
                        </a>

                        <a href="{{ route('payments.index') }}" class="p-2.5 rounded-xl border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/50 flex items-center gap-3 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <x-icon name="credit-card" class="w-4 h-4" />
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900">Payments & Invoices</p>
                                <p class="text-[11px] text-slate-400">Ledger & financial tracking</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Live Results Container -->
                <div id="spotlight-results" class="hidden space-y-4"></div>

                <!-- Loading State -->
                <div id="spotlight-loading" class="hidden py-8 text-center text-xs text-slate-400">
                    <div class="inline-block w-5 h-5 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin mb-2"></div>
                    <p>Searching studio records...</p>
                </div>

                <!-- Empty State -->
                <div id="spotlight-empty" class="hidden py-8 text-center text-xs text-slate-500">
                    <p class="font-bold text-slate-800">No matching studio records found</p>
                    <p class="text-slate-400 mt-1">Try searching by client name, shoot number, phone number, or venue.</p>
                </div>
            </div>

            <!-- Footer Keyboard Instructions -->
            <div class="px-4 py-2.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                <div class="flex items-center gap-3">
                    <span><kbd class="px-1 py-0.5 rounded bg-white border border-slate-200 font-mono text-[10px]">↑</kbd> <kbd class="px-1 py-0.5 rounded bg-white border border-slate-200 font-mono text-[10px]">↓</kbd> to navigate</span>
                    <span><kbd class="px-1 py-0.5 rounded bg-white border border-slate-200 font-mono text-[10px]">↵</kbd> to open</span>
                    <span><kbd class="px-1 py-0.5 rounded bg-white border border-slate-200 font-mono text-[10px]">ESC</kbd> to close</span>
                </div>
                <span class="font-medium text-slate-500">Studio OS Spotlight</span>
            </div>
        </div>
    </div>
</div>

<script>
    let spotlightDebounceTimer = null;
    let selectedResultIndex = -1;

    function openSpotlightSearch() {
        const modal = document.getElementById('spotlight-modal');
        const input = document.getElementById('spotlight-input');
        if (!modal) return;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            input.focus();
            input.select();
        }, 50);
    }

    function closeSpotlightSearch() {
        const modal = document.getElementById('spotlight-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Keyboard shortcut Cmd+K or Ctrl+K
    document.addEventListener('keydown', function(e) {
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            const modal = document.getElementById('spotlight-modal');
            if (modal && !modal.classList.contains('hidden')) {
                closeSpotlightSearch();
            } else {
                openSpotlightSearch();
            }
        }
        if (e.key === 'Escape') {
            closeSpotlightSearch();
        }
    });

    function handleSpotlightInput(val) {
        const query = val.trim();
        const shortcuts = document.getElementById('spotlight-shortcuts');
        const resultsEl = document.getElementById('spotlight-results');
        const loading = document.getElementById('spotlight-loading');
        const empty = document.getElementById('spotlight-empty');

        if (spotlightDebounceTimer) clearTimeout(spotlightDebounceTimer);

        if (query.length < 2) {
            shortcuts.classList.remove('hidden');
            resultsEl.classList.add('hidden');
            loading.classList.add('hidden');
            empty.classList.add('hidden');
            resultsEl.innerHTML = '';
            selectedResultIndex = -1;
            return;
        }

        shortcuts.classList.add('hidden');
        resultsEl.classList.add('hidden');
        empty.classList.add('hidden');
        loading.classList.remove('hidden');

        spotlightDebounceTimer = setTimeout(() => {
            fetch(`/search/global?q=${encodeURIComponent(query)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                loading.classList.add('hidden');
                if (data.total === 0) {
                    empty.classList.remove('hidden');
                    resultsEl.classList.add('hidden');
                    return;
                }

                empty.classList.add('hidden');
                resultsEl.classList.remove('hidden');

                let html = '';
                const groups = [
                    { key: 'shoots', title: 'Shoots & Bookings' },
                    { key: 'customers', title: 'Customers' },
                    { key: 'leads', title: 'Leads & Enquiries' },
                    { key: 'team', title: 'Studio Team' }
                ];

                groups.forEach(group => {
                    const items = data.results[group.key] || [];
                    if (items.length > 0) {
                        html += `
                            <div>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-2 py-1">${group.title} (${items.length})</p>
                                <div class="space-y-1 mt-1">
                                    ${items.map(item => `
                                        <a href="${item.url}" class="spotlight-item flex items-center justify-between p-2.5 rounded-xl hover:bg-indigo-50/80 transition-colors group">
                                            <div class="min-w-0 pr-3">
                                                <p class="text-xs font-bold text-slate-900 group-hover:text-indigo-700 truncate">${item.title}</p>
                                                <p class="text-[11px] text-slate-500 mt-0.5 truncate">${item.subtitle}</p>
                                            </div>
                                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 shrink-0">${item.badge}</span>
                                        </a>
                                    `).join('')}
                                </div>
                            </div>
                        `;
                    }
                });

                resultsEl.innerHTML = html;
                selectedResultIndex = -1;
            })
            .catch(err => {
                loading.classList.add('hidden');
                empty.classList.remove('hidden');
            });
        }, 180);
    }

    function handleSpotlightKeydown(e) {
        const items = document.querySelectorAll('.spotlight-item');
        if (!items || items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedResultIndex = (selectedResultIndex + 1) % items.length;
            updateSpotlightSelection(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedResultIndex = (selectedResultIndex - 1 + items.length) % items.length;
            updateSpotlightSelection(items);
        } else if (e.key === 'Enter') {
            if (selectedResultIndex >= 0 && items[selectedResultIndex]) {
                e.preventDefault();
                items[selectedResultIndex].click();
            }
        }
    }

    function updateSpotlightSelection(items) {
        items.forEach((item, idx) => {
            if (idx === selectedResultIndex) {
                item.classList.add('bg-indigo-50/90', 'border-indigo-200');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('bg-indigo-50/90', 'border-indigo-200');
            }
        });
    }

    function clearNotifications() {
        const badge = document.getElementById('notification-badge');
        if (badge) {
            badge.remove();
        }
        const dropdown = document.getElementById('notifications-dropdown');
        if (dropdown) {
            const countLabel = dropdown.querySelector('.rounded-full');
            if (countLabel) countLabel.remove();
        }
    }
</script>
