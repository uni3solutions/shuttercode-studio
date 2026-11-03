@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'icon' => null,
    'iconRight' => null,
    'type' => 'button'
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium transition-all duration-150 cursor-pointer disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden';

    $variants = [
        'primary' => 'bg-[#4F46E5] hover:bg-[#4338CA] text-white shadow-xs focus:ring-2 focus:ring-[#4F46E5]/25 border border-transparent',
        'secondary' => 'bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 border border-slate-200 shadow-2xs focus:ring-2 focus:ring-slate-200',
        'outline' => 'bg-transparent hover:bg-slate-100 text-slate-700 border border-slate-300',
        'danger' => 'bg-rose-600 hover:bg-rose-700 text-white shadow-xs focus:ring-2 focus:ring-rose-500/25 border border-transparent',
        'ghost' => 'bg-transparent hover:bg-slate-100 text-slate-600 hover:text-slate-900',
        'soft' => 'bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-100',
    ];

    $sizes = [
        'xs' => 'px-2 py-1 text-xs rounded-md gap-1',
        'sm' => 'px-2.5 py-1.5 text-xs rounded-lg gap-1.5',
        'md' => 'px-3.5 py-2 text-sm rounded-lg gap-2',
        'lg' => 'px-4 py-2.5 text-sm font-semibold rounded-lg gap-2',
    ];

    $iconSizes = [
        'xs' => 'w-3 h-3',
        'sm' => 'w-3.5 h-3.5',
        'md' => 'w-4 h-4',
        'lg' => 'w-4.5 h-4.5',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
    $iconClass = $iconSizes[$size] ?? 'w-4 h-4';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <x-icon :name="$icon" :class="$iconClass" />
        @endif
        {{ $slot }}
        @if($iconRight)
            <x-icon :name="$iconRight" :class="$iconClass" />
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <x-icon :name="$icon" :class="$iconClass" />
        @endif
        {{ $slot }}
        @if($iconRight)
            <x-icon :name="$iconRight" :class="$iconClass" />
        @endif
    </button>
@endif
