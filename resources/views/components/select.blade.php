@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'disabled' => false,
    'icon' => null,
    'helper' => null,
    'error' => null
])

@php
    $selectId = $attributes->get('id') ?? ($name ? $name . '_' . uniqid() : 'select_' . uniqid());
    $hasError = $error || ($name && $errors->has($name));
    $errorMessage = $error ?? ($name ? $errors->first($name) : null);
@endphp

<div class="w-full">
    @if($label)
        <label for="{{ $selectId }}" class="block text-xs font-semibold text-slate-700 mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative rounded-lg shadow-2xs">
        @if($icon)
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <x-icon :name="$icon" class="w-4 h-4" />
            </div>
        @endif

        <select
            name="{{ $name }}"
            id="{{ $selectId }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            {{ $attributes->merge([
                'class' => 'block w-full rounded-lg border text-sm transition-all duration-150 disabled:bg-slate-50 disabled:text-slate-400 disabled:cursor-not-allowed bg-white ' .
                ($icon ? 'pl-9 ' : 'pl-3.5 ') .
                'pr-8 py-2 appearance-none ' .
                ($hasError 
                    ? 'border-rose-300 text-rose-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                    : 'border-slate-300/90 text-slate-900 focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15 focus:outline-hidden')
            ]) }}
        >
            {{ $slot }}
        </select>

        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
            <x-icon name="chevron-down" class="w-4 h-4" />
        </div>
    </div>

    @if($hasError)
        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $errorMessage }}</p>
    @elseif($helper)
        <p class="mt-1 text-xs text-slate-500">{{ $helper }}</p>
    @endif
</div>
