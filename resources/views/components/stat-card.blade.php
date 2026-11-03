@props([
    'title',
    'value',
    'icon' => 'activity',
    'iconColor' => 'indigo',
    'trend' => null,
    'trendUp' => true,
    'subtitle' => null,
    'href' => null
])

@php
    $colorMap = [
        'indigo' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'border' => 'border-indigo-100'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-100'],
        'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-100'],
        'rose' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'border' => 'border-rose-100'],
        'sky' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-600', 'border' => 'border-sky-100'],
        'purple' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-600', 'border' => 'border-purple-100'],
    ];

    $colors = $colorMap[$iconColor] ?? $colorMap['indigo'];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs hover:border-slate-300 transition-all duration-150']) }}>
    <div class="flex items-start justify-between">
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-slate-500">{{ $title }}</p>
            <h4 class="text-2xl font-bold text-slate-900 mt-1.5 tracking-tight">{{ $value }}</h4>
        </div>
        <div class="w-10 h-10 rounded-lg {{ $colors['bg'] }} {{ $colors['border'] }} border flex items-center justify-center shrink-0">
            <x-icon :name="$icon" class="w-5 h-5 {{ $colors['text'] }}" />
        </div>
    </div>

    @if($trend || $subtitle)
        <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
            @if($trend)
                <span class="inline-flex items-center gap-1 font-medium {{ $trendUp ? 'text-emerald-600' : 'text-rose-600' }}">
                    <x-icon :name="$trendUp ? 'trending-up' : 'trending-down'" class="w-3.5 h-3.5" />
                    {{ $trend }}
                </span>
            @endif
            @if($subtitle)
                <span class="text-slate-400">{{ $subtitle }}</span>
            @endif
        </div>
    @endif
</div>
