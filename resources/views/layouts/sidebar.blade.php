@php
    $currentPath = request()->path();
    $currentUser = auth()->user();
    $studioSettings = \App\Models\StudioSetting::current();

    $shootsBadgeCount = $currentUser?->isTelecaller()
        ? \App\Models\Shoot::forTelecaller($currentUser->id)->whereNotIn('status', ['completed', 'cancelled'])->count()
        : \App\Models\Shoot::whereNotIn('status', ['completed', 'cancelled'])->count();

    $newLeadsBadgeCount = $currentUser?->isTelecaller()
        ? \App\Models\Lead::where(function ($q) use ($currentUser) {
            $q->where('assigned_to', $currentUser->id)
                ->orWhere('created_by', $currentUser->id);
        })->where('status', 'new')->count()
        : \App\Models\Lead::where('status', 'new')->count();

    $navItems = [
        [
            'title' => 'Dashboard',
            'url' => '/',
            'icon' => 'layout-dashboard',
            'badge' => null,
        ],
        [
            'title' => 'Shoots & Bookings',
            'url' => '/shoots',
            'icon' => 'camera',
            'badge' => $shootsBadgeCount ?: null,
        ],
        [
            'title' => 'Customers',
            'url' => '/customers',
            'icon' => 'users',
            'badge' => null,
        ],
        [
            'title' => 'Leads & Enquiries',
            'url' => '/leads',
            'icon' => 'message-square-quote',
            'badge' => $newLeadsBadgeCount ? 'New' : null,
        ],
        [
            'title' => 'Photography Packages',
            'url' => '/packages',
            'icon' => 'layers',
            'badge' => null,
        ],
        [
            'title' => 'Photographers',
            'url' => '/photographers',
            'icon' => 'camera',
            'badge' => null
        ],
        [
            'title' => 'Crew & Staff',
            'url' => '/crew',
            'icon' => 'user-check',
            'badge' => null
        ],
        [
            'title' => 'Payments Ledger',
            'url' => '/payments',
            'icon' => 'credit-card',
            'badge' => null
        ],
        [
            'title' => 'Invoices & Billing',
            'url' => '/invoices',
            'icon' => 'file-text',
            'badge' => null
        ],
        [
            'title' => 'Reports & Analytics',
            'url' => '/reports',
            'icon' => 'bar-chart-3',
            'badge' => null
        ],
        [
            'title' => 'Studio Settings',
            'url' => '/settings',
            'icon' => 'settings',
            'badge' => null
        ],
    ];

    if ($currentUser?->isAdmin()) {
        $navItems[] = [
            'title' => 'Shoot Types',
            'url' => '/shoot-types',
            'icon' => 'tag',
            'badge' => null,
        ];
    }
@endphp

<!-- Sidebar Backdrop for Mobile -->
<div id="sidebar-backdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-40 lg:hidden hidden" onclick="toggleSidebar()"></div>

<!-- Sidebar Container -->
<aside id="sidebar" class="fixed top-0 bottom-0 left-0 z-40 w-64 bg-white border-r border-slate-200 flex flex-col transition-transform duration-200 ease-in-out -translate-x-full lg:translate-x-0">
    <!-- Brand Header -->
    <div class="h-16 px-5 flex items-center justify-between border-b border-slate-100">
        <a href="{{ $currentUser?->isPhotographer() ? '/my-shoots' : '/' }}" class="flex items-center gap-3 group min-w-0" title="{{ $studioSettings->studio_name }}">
            @if($studioSettings->logo_url)
                <div style="height: 38px; max-height: 38px; max-width: 185px; display: flex; align-items: center; justify-content: flex-start; overflow: hidden;">
                    <img src="{{ $studioSettings->logo_url }}" 
                         alt="{{ $studioSettings->studio_name }}" 
                         class="group-hover:opacity-90 transition-opacity"
                         style="max-height: 38px; max-width: 185px; width: auto; height: auto; object-fit: contain; display: block;">
                </div>
            @else
                <div class="w-9 h-9 rounded-xl bg-[#4F46E5] flex items-center justify-center text-white shadow-xs group-hover:scale-105 transition-transform shrink-0"
                     style="width: 36px; height: 36px; min-width: 36px; background-color: #4f46e5; border-radius: 0.75rem;">
                    <x-icon name="aperture" class="w-5 h-5 text-white" />
                </div>
                <div class="min-w-0">
                    <span class="font-bold text-slate-900 text-[15px] tracking-tight block truncate">{{ $studioSettings->studio_name ?: 'ShutterCode' }}</span>
                    <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider block -mt-1 truncate">Studio OS</span>
                </div>
            @endif
        </a>
        <button type="button" class="lg:hidden p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 shrink-0" onclick="toggleSidebar()">
            <x-icon name="x" class="w-4 h-4" />
        </button>
    </div>

    <!-- Navigation Links -->
    <div class="flex-1 overflow-y-auto px-3.5 py-4 space-y-1">
        @if($currentUser?->isPhotographer())
            <div class="px-2 pb-2 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                Photographer Workspace
            </div>
            
            <a 
                href="/my-dashboard" 
                class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ str_starts_with('/' . $currentPath, '/my-dashboard') ? 'bg-indigo-50/80 text-[#4F46E5] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
            >
                <div class="flex items-center gap-3">
                    <x-icon name="layout-dashboard" class="w-4 h-4 {{ str_starts_with('/' . $currentPath, '/my-dashboard') ? 'text-[#4F46E5]' : 'text-slate-400' }}" />
                    <span>Dashboard</span>
                </div>
            </a>

            <a 
                href="/my-shoots" 
                class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ str_starts_with('/' . $currentPath, '/my-shoots') ? 'bg-indigo-50/80 text-[#4F46E5] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
            >
                <div class="flex items-center gap-3">
                    <x-icon name="camera" class="w-4 h-4 {{ str_starts_with('/' . $currentPath, '/my-shoots') ? 'text-[#4F46E5]' : 'text-slate-400' }}" />
                    <span>Assigned Shoots</span>
                </div>
                @php
                    $pendingShootsCount = \App\Models\ShootAssignment::where('user_id', $currentUser->id)
                        ->whereHas('shoot', fn($q) => $q->whereNotIn('status', ['completed', 'cancelled']))
                        ->count();
                @endphp
                @if($pendingShootsCount > 0)
                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-md bg-indigo-100 text-indigo-700">
                        {{ $pendingShootsCount }}
                    </span>
                @endif
            </a>

            <a 
                href="/my-payments" 
                class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ str_starts_with('/' . $currentPath, '/my-payments') ? 'bg-indigo-50/80 text-[#4F46E5] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
            >
                <div class="flex items-center gap-3">
                    <x-icon name="credit-card" class="w-4 h-4 {{ str_starts_with('/' . $currentPath, '/my-payments') ? 'text-[#4F46E5]' : 'text-slate-400' }}" />
                    <span>Payouts & Invoices</span>
                </div>
            </a>
        @else
            <div class="px-2 pb-2 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                Studio Management
            </div>

            @foreach($navItems as $item)
                @php
                    $isActive = ($item['url'] === '/' && $currentPath === '/') || ($item['url'] !== '/' && str_starts_with('/' . $currentPath, $item['url']));
                @endphp
                <a 
                    href="{{ $item['url'] }}" 
                    class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ $isActive ? 'bg-indigo-50/80 text-[#4F46E5] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                >
                    <div class="flex items-center gap-3">
                        <x-icon :name="$item['icon']" class="w-4 h-4 {{ $isActive ? 'text-[#4F46E5]' : 'text-slate-400' }}" />
                        <span>{{ $item['title'] }}</span>
                    </div>
                    @if($item['badge'])
                        <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-md {{ $item['badge'] === 'New' ? 'bg-emerald-100 text-emerald-700' : 'bg-indigo-100 text-indigo-700' }}">
                            {{ $item['badge'] }}
                        </span>
                    @endif
                </a>
            @endforeach

            <div class="pt-4 px-2 pb-2 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                Photographer Portal
            </div>
            <a 
                href="/my-shoots" 
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ str_starts_with('/' . $currentPath, '/my-shoots') ? 'bg-indigo-50/80 text-[#4F46E5] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
            >
                <x-icon name="camera" class="w-4 h-4 {{ str_starts_with('/' . $currentPath, '/my-shoots') ? 'text-[#4F46E5]' : 'text-slate-400' }}" />
                <span>My Assigned Shoots</span>
            </a>
        @endif
    </div>

    <!-- Active User Footer Profile -->
    <div class="p-3.5 border-t border-slate-100 bg-slate-50/60">
        <div class="flex items-center justify-between p-2 rounded-xl bg-white border border-slate-200/80 shadow-2xs">
            <div class="flex items-center gap-2.5 overflow-hidden">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                    {{ substr($currentUser?->name ?? 'Admin', 0, 2) }}
                </div>
                <div class="truncate">
                    <p class="text-xs font-semibold text-slate-900 truncate">{{ $currentUser?->name ?? 'Super Admin' }}</p>
                    <p class="text-[11px] text-slate-400 truncate capitalize">{{ str_replace('_', ' ', $currentUser?->role ?? 'admin') }}</p>
                </div>
            </div>
            <a href="{{ route('logout') }}" title="Sign out" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-md hover:bg-rose-50 transition-colors">
                <x-icon name="log-out" class="w-4 h-4" />
            </a>
        </div>
    </div>
</aside>
