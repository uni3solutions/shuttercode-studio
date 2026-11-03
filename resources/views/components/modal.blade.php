@props([
    'id',
    'title' => null,
    'subtitle' => null,
    'maxWidth' => 'md'
])

@php
    $widths = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
    ];
    $widthClass = $widths[$maxWidth] ?? $widths['md'];
@endphp

<div id="{{ $id }}" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="{{ $id }}-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity" onclick="document.getElementById('{{ $id }}').classList.add('hidden')"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all w-full {{ $widthClass }} border border-slate-200">
            @if($title)
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900" id="{{ $id }}-title">{{ $title }}</h3>
                        @if($subtitle)
                            <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                        @endif
                    </div>
                    <button type="button" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors" onclick="document.getElementById('{{ $id }}').classList.add('hidden')">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>
            @endif

            <div class="p-6">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
