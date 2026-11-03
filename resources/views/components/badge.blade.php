@props([
    'variant' => 'neutral',
    'dot' => false,
    'size' => 'sm'
])

@php
    $variants = [
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
        'warning' => 'bg-amber-50 text-amber-700 border-amber-200/80',
        'danger' => 'bg-rose-50 text-rose-700 border-rose-200/80',
        'info' => 'bg-indigo-50 text-indigo-700 border-indigo-200/80',
        'sky' => 'bg-sky-50 text-sky-700 border-sky-200/80',
        'purple' => 'bg-purple-50 text-purple-700 border-purple-200/80',
        'neutral' => 'bg-slate-100 text-slate-700 border-slate-200',
    ];

    $dotColors = [
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-rose-500',
        'info' => 'bg-indigo-500',
        'sky' => 'bg-sky-500',
        'purple' => 'bg-purple-500',
        'neutral' => 'bg-slate-400',
    ];

    $sizes = [
        'xs' => 'px-2 py-0.5 text-[11px]',
        'sm' => 'px-2.5 py-1 text-xs',
        'md' => 'px-3 py-1.5 text-xs font-semibold',
    ];

    $classes = 'inline-flex items-center gap-1.5 font-medium rounded-full border whitespace-nowrap shrink-0 ' . ($variants[$variant] ?? $variants['neutral']) . ' ' . ($sizes[$size] ?? $sizes['sm']);
    $dotClass = $dotColors[$variant] ?? $dotColors['neutral'];
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
    @endif
    {{ $slot }}
</span>
