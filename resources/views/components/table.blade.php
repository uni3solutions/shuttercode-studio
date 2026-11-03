@props([
    'headers' => [],
    'empty' => false,
    'emptyMessage' => 'No records found.',
    'emptyIcon' => 'inbox'
])

<div class="overflow-x-auto rounded-xl border border-slate-200/80 bg-white shadow-2xs">
    <table {{ $attributes->merge(['class' => 'w-full text-left border-collapse text-sm']) }}>
        @if(!empty($headers))
            <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                <tr>
                    @foreach($headers as $header)
                        <th scope="col" class="px-4 py-3 first:pl-5 last:pr-5">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        
        <tbody class="divide-y divide-slate-100 text-slate-700">
            @if($empty)
                <tr>
                    <td colspan="{{ count($headers) ?: 10 }}" class="px-5 py-12 text-center text-slate-400">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                <x-icon :name="$emptyIcon" class="w-5 h-5" />
                            </div>
                            <p class="text-sm font-medium text-slate-600">{{ $emptyMessage }}</p>
                        </div>
                    </td>
                </tr>
            @else
                {{ $slot }}
            @endif
        </tbody>
    </table>
</div>
