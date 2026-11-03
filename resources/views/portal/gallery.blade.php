<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $shoot->title }} — Private Client Showcase &bull; ShutterCode Studio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .font-serif { font-family: 'Playfair Display', serif; }
        .font-sans { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-full bg-[#090D16] text-slate-100 font-sans antialiased selection:bg-indigo-600 selection:text-white">

    <!-- Top Floating Navigation -->
    <header class="fixed top-0 inset-x-0 z-40 bg-[#090D16]/80 backdrop-blur-md border-b border-white/10 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold shadow-md shadow-indigo-600/30">
                <x-icon name="aperture" class="w-4 h-4" />
            </div>
            <div>
                <span class="font-bold text-white text-sm tracking-tight">ShutterCode Private Portal</span>
                <span class="text-[10px] text-slate-400 block font-mono">Shoot ID: {{ $shoot->shoot_number }}</span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Client Protected
            </span>
            <a href="/" class="text-xs font-medium text-slate-400 hover:text-white px-3 py-1.5 rounded-lg border border-white/10 hover:bg-white/5 transition-colors">
                Studio Admin &rarr;
            </a>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 px-4 sm:px-6 lg:px-8 text-center max-w-5xl mx-auto overflow-hidden">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-semibold mb-6">
            <span>{{ $shoot->event_type }} Premium Collection</span>
            <span>&bull;</span>
            <span>{{ $shoot->shoot_date->format('F d, Y') }}</span>
        </div>

        <h1 class="text-4xl sm:text-5xl md:text-6xl font-serif font-bold text-white tracking-tight leading-tight mb-4">
            {{ $shoot->title }}
        </h1>

        <p class="text-sm sm:text-base text-slate-400 max-w-2xl mx-auto mb-8 font-light leading-relaxed">
            Captured with love by ShutterCode Studio at <span class="text-slate-200 font-medium">{{ $shoot->venue }}</span>. Every moment, emotion, and detail preserved in ultra high-resolution clarity.
        </p>

        <!-- Access / Download Controls -->
        <div class="flex flex-wrap items-center justify-center gap-3 max-w-md mx-auto">
            @if($shoot->delivery?->raw_drive_link)
                <a 
                    href="{{ $shoot->delivery->raw_drive_link }}" 
                    target="_blank" 
                    class="flex-1 min-w-[200px] inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs transition-all shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50"
                >
                    <x-icon name="download-cloud" class="w-4 h-4" />
                    <span>Download RAW Photos ({{ $shoot->delivery->raw_photos_count ?? '1200' }}+)</span>
                </a>
            @endif

            <button 
                type="button" 
                onclick="document.getElementById('pin-info-modal').classList.toggle('hidden')" 
                class="inline-flex items-center gap-2 px-4 py-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-white font-medium text-xs transition-colors cursor-pointer"
            >
                <x-icon name="key" class="w-4 h-4 text-amber-400" />
                <span>Gallery PIN: <strong class="font-mono text-amber-300 ml-1">{{ $shoot->delivery?->gallery_pin ?? '4829' }}</strong></span>
            </button>
        </div>
    </section>

    <!-- Interactive Curated Showcase Gallery Grid -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-28">
        <div class="flex items-center justify-between pb-4 border-b border-white/10 mb-8">
            <div>
                <h3 class="text-base font-bold text-white tracking-tight">Curated Master Highlights</h3>
                <p class="text-xs text-slate-400">Handcrafted edits approved by lead director</p>
            </div>
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <x-icon name="image" class="w-4 h-4 text-indigo-400" />
                <span>8 Master Frames</span>
            </div>
        </div>

        <!-- High-End Photo Masonry Showcase -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $samplePhotos = [
                    ['url' => 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=1000&q=80', 'title' => 'The Grand Vows', 'sub' => 'Main Ceremony'],
                    ['url' => 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=1000&q=80', 'title' => 'Golden Hour Glow', 'sub' => 'Outdoor Portraits'],
                    ['url' => 'https://images.unsplash.com/photo-1606800052052-a08af7148866?auto=format&fit=crop&w=1000&q=80', 'title' => 'Intimate Moments', 'sub' => 'Candid Emotion'],
                    ['url' => 'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=1000&q=80', 'title' => 'Heritage Details', 'sub' => 'Bridal Jewellery'],
                    ['url' => 'https://images.unsplash.com/photo-1544077960-604201fe74bc?auto=format&fit=crop&w=1000&q=80', 'title' => 'Celebration Joy', 'sub' => 'Reception Entrance'],
                    ['url' => 'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=1000&q=80', 'title' => 'First Dance', 'sub' => 'Twilight Sangeet'],
                    ['url' => 'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?auto=format&fit=crop&w=1000&q=80', 'title' => 'The Ring Exchange', 'sub' => 'Macro Precision'],
                    ['url' => 'https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=1000&q=80', 'title' => 'Joyous Confetti', 'sub' => 'Grand Finale'],
                ];
            @endphp

            @foreach($samplePhotos as $photo)
                <div class="group relative rounded-2xl overflow-hidden bg-slate-900 border border-white/10 aspect-3/4 hover:border-indigo-500/50 transition-all duration-300">
                    <img 
                        src="{{ $photo['url'] }}" 
                        alt="{{ $photo['title'] }}" 
                        loading="lazy" 
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/20 to-transparent opacity-80 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-4">
                        <span class="text-[10px] uppercase font-bold text-indigo-400 tracking-wider block mb-0.5">{{ $photo['sub'] }}</span>
                        <h4 class="text-sm font-bold text-white">{{ $photo['title'] }}</h4>
                        <div class="mt-2.5 flex items-center justify-between text-xs text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity">
                            <span class="text-[11px] font-mono">Full Res JPEG</span>
                            <span class="inline-flex items-center gap-1 text-indigo-300 font-semibold hover:underline cursor-pointer">
                                <span>Preview</span>
                                <x-icon name="arrow-up-right" class="w-3 h-3" />
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Album Proofing Callout -->
        <div class="mt-12 p-8 rounded-3xl bg-linear-to-r from-indigo-950/40 via-purple-950/30 to-slate-900 border border-white/10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-1">
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-400">Handcrafted Leather Lay-Flat Album</span>
                <h3 class="text-xl font-bold text-white">Your Hardcover Photobook is In Production</h3>
                <p class="text-xs text-slate-400 max-w-xl">
                    Every spread has been color calibrated and printed on Fuji Silk 300 GSM photographic paper. Estimated dispatch within 5 business days.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a 
                    href="https://wa.me/919876543210?text={{ urlencode('Hi ShutterCode team, I loved our gallery for ' . $shoot->shoot_number . '! Here is our feedback:') }}" 
                    target="_blank" 
                    class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs transition-colors"
                >
                    <x-icon name="message-circle" class="w-4 h-4" />
                    <span>WhatsApp Feedback to Studio</span>
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-white/10 py-8 text-center text-xs text-slate-500">
        <p class="font-medium text-slate-400">ShutterCode Studio &bull; Premium Cinematic Wedding & Event Photography</p>
        <p class="text-[11px] mt-1 text-slate-600">All rights reserved. Delivered with end-to-end cloud protection.</p>
    </footer>

</body>
</html>
