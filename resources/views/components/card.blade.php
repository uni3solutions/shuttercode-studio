@props([
    'title' => null,
    'subtitle' => null,
    'action' => null,
    'footer' => null,
    'noPadding' => false
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden']) }}>
    @if($title || $action)
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-4">
            <div>
                @if($title)
                    <h3 class="text-sm font-semibold text-slate-900">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @if($action)
                <div class="flex items-center gap-2">
                    {{ $action }}
                </div>
            @endif
        </div>
    @endif

    <div @class(['p-5' => !$noPadding, 'p-0' => $noPadding])>
        {{ $slot }}
    </div>

    @if($footer)
        <div class="px-5 py-3.5 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            {{ $footer }}
        </div>
    @endif
</div>
