@props([
    'phone' => null,
    'url' => null,
    'label' => 'WhatsApp',
    'size' => 'xs'
])

@php
    $href = $url;
    if (!$href && $phone) {
        $clean = preg_replace('/[^0-9]/', '', (string)$phone);
        if (strlen($clean) === 10) {
            $clean = '91' . $clean;
        }
        $href = 'https://wa.me/' . $clean;
    }

    $sizes = [
        'xs' => 'px-2.5 py-1 text-xs gap-1.5',
        'sm' => 'px-3 py-2 text-xs gap-1.5',
        'md' => 'px-3.5 py-2 text-sm gap-2',
    ];

    $iconSizes = [
        'xs' => 'w-3.5 h-3.5',
        'sm' => 'w-4 h-4',
        'md' => 'w-4 h-4',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['xs'];
    $iconClass = $iconSizes[$size] ?? 'w-3.5 h-3.5';
@endphp

@if($href)
    <a 
        href="{{ $href }}" 
        target="_blank" 
        rel="noopener noreferrer" 
        title="Chat on WhatsApp" 
        {{ $attributes->merge(['class' => 'inline-flex items-center font-semibold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors shrink-0 ' . $sizeClass]) }}
    >
        <x-icon name="message-circle" class="{{ $iconClass }} text-emerald-600" />
        <span>{{ $label }}</span>
    </a>
@endif
