<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} — {{ config('app.name', 'ShutterCode Studio') }}</title>

    <!-- Inter Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles & Scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-[#F8FAFC]">
    <div class="min-h-full">
        <!-- Sidebar Navigation -->
        @include('layouts.sidebar')

        <!-- Main Content Area -->
        <div class="lg:pl-64 flex flex-col min-h-screen">
            <!-- Top App Bar -->
            @include('layouts.topbar', ['title' => $title ?? 'Dashboard', 'breadcrumbs' => $breadcrumbs ?? []])

            <!-- Page Body -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @if(session('success'))
                    <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <x-icon name="check-circle" class="w-4 h-4 text-emerald-600 shrink-0" />
                            <span>{{ session('success') }}</span>
                        </div>
                        <button type="button" class="text-emerald-600 hover:text-emerald-800" onclick="this.parentElement.remove()">
                            <x-icon name="x" class="w-4 h-4" />
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <x-icon name="alert-circle" class="w-4 h-4 text-rose-600 shrink-0" />
                            <span>{{ session('error') }}</span>
                        </div>
                        <button type="button" class="text-rose-600 hover:text-rose-800" onclick="this.parentElement.remove()">
                            <x-icon name="x" class="w-4 h-4" />
                        </button>
                    </div>
                @endif

                {{ $slot ?? '' }}
                @yield('content')
            </main>

            <!-- Compact Footer -->
            <footer class="mt-auto px-6 py-4 border-t border-slate-200/80 bg-white/50 text-xs text-slate-400 flex flex-col sm:flex-row items-center justify-between gap-2">
                <div>
                    <span>&copy; {{ date('Y') }} ShutterCode Studio. Modern Photography Management OS.</span>
                </div>
                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center gap-1.5 text-emerald-600 font-medium">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> System Operational
                    </span>
                    <a href="/support" class="hover:text-slate-600">Help & Support</a>
                </div>
            </footer>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm pointer-events-none"></div>

    <!-- UI Scripts for Interactions -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');
        }

        function toggleDropdown(id) {
            const dropdown = document.getElementById(id);
            if (!dropdown) return;
            const isHidden = dropdown.classList.contains('hidden');
            // Close any other open dropdowns first
            document.querySelectorAll('[id$="-dropdown"]').forEach(el => el.classList.add('hidden'));
            if (isHidden) {
                dropdown.classList.remove('hidden');
            }
        }

        // Close dropdowns on click outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#notifications-dropdown-container') && !e.target.closest('#user-menu-container')) {
                document.querySelectorAll('[id$="-dropdown"]').forEach(el => el.classList.add('hidden'));
            }
        });
    </script>
</body>
</html>
