<!DOCTYPE html>
<html lang="en" class="h-full bg-[#F8FAFC]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — ShutterCode Studio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .font-display { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
@php
    $studioSettings = \App\Models\StudioSetting::current();
@endphp
<body class="h-full bg-[#F6F8FC] antialiased text-slate-900 selection:bg-indigo-600 selection:text-white flex items-center justify-center p-4 sm:p-6 lg:p-8">
    
    <!-- Main Shell Container -->
    <div class="w-full max-w-6xl bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-200/80 overflow-hidden grid lg:grid-cols-12 min-h-[640px]">
        
        <!-- Left Side: Soft Aurora Mesh with Floating Team & Studio Cards (Matches Reference) -->
        <div class="hidden lg:flex lg:col-span-6 xl:col-span-7 relative p-10 xl:p-14 flex-col justify-between overflow-hidden bg-gradient-to-br from-[#EEF2FF] via-[#F3E8FF]/60 to-[#E0E7FF]/70 border-r border-slate-100">
            
            <!-- Soft Organic Ambient Gradient Orbs -->
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-purple-300/40 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[520px] h-[520px] bg-indigo-200/50 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-20 -right-20 w-80 h-80 bg-blue-300/30 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Top Left Studio Badge -->
            <div class="relative z-10 flex items-center gap-2 text-xs font-semibold text-indigo-700 bg-white/80 backdrop-blur-md px-3.5 py-1.5 rounded-full border border-white/60 shadow-2xs w-fit">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Production OS • 2026 Edition</span>
            </div>

            <!-- Center Stage: Floating Studio Stats & Team Cards (Arranged like Reference) -->
            <div class="relative z-10 my-auto py-6">
                <div class="relative w-full max-w-md mx-auto h-[380px]">
                    
                    <!-- 1. Vibrant Purple Stat Badge (Top Left, like "25K+ Leads") -->
                    <div class="absolute top-2 left-0 z-20 p-4 rounded-2xl bg-gradient-to-br from-[#7C3AED] to-[#6366F1] text-white shadow-xl shadow-indigo-500/25 border border-white/20 transition-transform hover:-translate-y-1 duration-200">
                        <div class="text-2xl font-black font-display tracking-tight leading-none">150+</div>
                        <div class="text-[11px] font-medium text-indigo-100 mt-1">Active Shoots Delivered</div>
                        <div class="text-[10px] text-indigo-200/80 font-mono mt-0.5">Bangalore &amp; Mumbai</div>
                    </div>

                    <!-- 2. Hero Floating Card: Carl Wright / Rahul Rao (Top Right) -->
                    <div class="absolute top-0 right-0 z-10 w-52 p-3.5 rounded-2xl bg-white/90 backdrop-blur-md border border-white/80 shadow-lg shadow-indigo-950/5 transition-transform hover:-translate-y-1 duration-200">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-600 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                RR
                            </div>
                            <div class="overflow-hidden">
                                <h4 class="text-xs font-bold text-slate-900 truncate">Rahul Rao</h4>
                                <p class="text-[10px] text-slate-500 font-medium truncate">Studio Director</p>
                                <p class="text-[9px] text-slate-400 font-mono truncate">superadmin@shuttercode.com</p>
                            </div>
                        </div>
                        <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px]">
                            <span class="text-slate-400">Total Revenue</span>
                            <span class="font-bold text-emerald-600 font-mono">₹45 Lakhs ↗</span>
                        </div>
                    </div>

                    <!-- 3. Primary Center Card: Anna Taylor / Neha Sharma (Center Hero) -->
                    <div class="absolute top-28 left-6 z-30 w-64 p-4 rounded-2xl bg-white/95 backdrop-blur-md border border-white shadow-xl shadow-indigo-950/10 transition-transform hover:-translate-y-1 duration-200">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-full bg-gradient-to-tr from-amber-500 to-orange-500 text-white font-bold text-sm flex items-center justify-center shrink-0 shadow-2xs">
                                NS
                            </div>
                            <div class="overflow-hidden">
                                <h4 class="text-sm font-bold text-slate-900 truncate">Neha Sharma</h4>
                                <p class="text-xs text-slate-500 font-medium truncate">Lead Telecaller</p>
                                <p class="text-[10px] text-slate-400 font-mono truncate">neha@shuttercode.com</p>
                            </div>
                        </div>
                        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] text-slate-400">Client Conversions</span>
                            <span class="text-xs font-bold text-indigo-600 font-mono">88.5% Rate ↗</span>
                        </div>
                    </div>

                    <!-- 4. Bottom Left Floating Card: Joan Dalton / Vikram Malhotra -->
                    <div class="absolute bottom-4 left-0 z-20 w-48 p-3 rounded-2xl bg-white/90 backdrop-blur-md border border-white/80 shadow-md shadow-indigo-950/5 transition-transform hover:-translate-y-1 duration-200">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-slate-800 text-white font-bold text-[11px] flex items-center justify-center shrink-0">
                                VM
                            </div>
                            <div class="overflow-hidden">
                                <h5 class="text-[11px] font-bold text-slate-900 truncate">Vikram Malhotra</h5>
                                <p class="text-[10px] text-slate-500 truncate">Studio Operations</p>
                            </div>
                        </div>
                        <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[10px]">
                            <span class="text-slate-400">SLA Delivery</span>
                            <span class="font-bold text-emerald-600 font-mono">99.4% ↗</span>
                        </div>
                    </div>

                    <!-- 5. Bottom Right Floating Card: Michael King / Arun Kumar -->
                    <div class="absolute bottom-2 right-2 z-20 w-52 p-3 rounded-2xl bg-white/90 backdrop-blur-md border border-white/80 shadow-md shadow-indigo-950/5 transition-transform hover:-translate-y-1 duration-200">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-emerald-600 text-white font-bold text-[11px] flex items-center justify-center shrink-0">
                                AK
                            </div>
                            <div class="overflow-hidden">
                                <h5 class="text-[11px] font-bold text-slate-900 truncate">Arun Kumar</h5>
                                <p class="text-[10px] text-slate-500 truncate">Lead Candid Photographer</p>
                            </div>
                        </div>
                        <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[10px]">
                            <span class="text-slate-400">Client Rating</span>
                            <span class="font-bold text-amber-500 font-mono">4.98 ★ (96)</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Bottom Studio Credits -->
            <div class="relative z-10 flex items-center justify-between text-xs text-slate-500 pt-4 border-t border-indigo-100/60 font-medium">
                <span>{{ $studioSettings->studio_name ?: 'ShutterCode Production' }}</span>
                <span>Fine Art Photography Studio</span>
            </div>
        </div>

        <!-- Right Side: Clean, High-End Sign In (Matches Reference) -->
        <div class="lg:col-span-6 xl:col-span-5 p-8 sm:p-12 xl:p-14 flex flex-col justify-between bg-white">
            
            <div class="w-full max-w-sm mx-auto my-auto space-y-6">
                
                <!-- Studio Logo -->
                <div class="text-center">
                    @if($studioSettings->logo_url)
                        <img src="{{ $studioSettings->logo_url }}" 
                             alt="{{ $studioSettings->studio_name }}" 
                             class="h-9 w-auto mx-auto object-contain mb-4">
                    @else
                        <div class="inline-flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold">
                                <x-icon name="aperture" class="w-4 h-4" />
                            </div>
                            <span class="font-display font-extrabold text-lg tracking-tight text-slate-900">SHUTTERCODE</span>
                        </div>
                    @endif

                    <h1 class="font-display text-2xl font-bold tracking-tight text-slate-900 mt-2">
                        Welcome Back!
                    </h1>
                    <p class="text-xs text-slate-500 mt-1">
                        Sign in to your studio account to continue
                    </p>
                </div>

                <!-- Session Alerts -->
                @if(session('success'))
                    <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-medium flex items-center gap-2">
                        <x-icon name="check-circle" class="w-4 h-4 text-emerald-600 shrink-0" />
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 font-medium flex items-center gap-2">
                        <x-icon name="alert-circle" class="w-4 h-4 text-rose-600 shrink-0" />
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 font-medium flex items-center gap-2">
                        <x-icon name="alert-circle" class="w-4 h-4 text-rose-600 shrink-0" />
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <!-- Sign In Form -->
                <form action="{{ route('login.post') }}" method="POST" class="space-y-4 pt-2">
                    @csrf
                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Email address
                        </label>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            required 
                            value="{{ old('email') }}"
                            class="block w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 placeholder-slate-400 focus:outline-hidden focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all"
                            placeholder="name@shuttercode.com"
                            autocomplete="email"
                        />
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-semibold text-slate-700">
                                Password
                            </label>
                            <a href="#" class="text-xs font-medium text-indigo-600 hover:text-indigo-700 transition-colors">
                                Forgot password?
                            </a>
                        </div>
                        <div class="relative">
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                required 
                                class="block w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 placeholder-slate-400 focus:outline-hidden focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all pr-10"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                            />
                            <button 
                                type="button" 
                                onclick="togglePasswordVisibility()" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 cursor-pointer"
                                title="Toggle password visibility"
                            >
                                <x-icon name="eye" id="eye-icon" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center text-xs pt-0.5">
                        <label class="flex items-center gap-2 cursor-pointer text-slate-600 select-none">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span>Keep me signed in</span>
                        </label>
                    </div>

                    <!-- Vibrant Action Button (Matches Reference Lilac/Purple Gradient) -->
                    <button 
                        type="submit" 
                        class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-gradient-to-r from-[#818CF8] via-[#6366F1] to-[#4F46E5] hover:from-[#6366F1] hover:to-[#4338CA] active:scale-[0.99] text-white text-sm font-semibold shadow-md shadow-indigo-500/25 transition-all cursor-pointer mt-2"
                    >
                        <span>Continue</span>
                        <x-icon name="arrow-right" class="w-4 h-4" />
                    </button>
                </form>

            </div>

            <!-- Footer -->
            <div class="text-center text-xs text-slate-400 pt-6">
                &copy; {{ date('Y') }} ShutterCode Studio OS. All rights reserved.
            </div>

        </div>

    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            if (!passwordInput) return;
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        }
    </script>
</body>
</html>
