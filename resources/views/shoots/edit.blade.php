@extends('layouts.app', [
    'title' => 'Edit Shoot — ' . $shoot->shoot_number,
    'breadcrumbs' => ['Home' => '/', 'Shoots' => route('shoots.index'), $shoot->shoot_number => route('shoots.show', $shoot), 'Edit' => '#']
])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Edit Shoot Details</h2>
            <p class="text-xs text-slate-500 mt-0.5">Modify event schedule, pricing, or requirements for {{ $shoot->shoot_number }}</p>
        </div>
        <x-button variant="secondary" size="sm" icon="arrow-left" href="{{ route('shoots.show', $shoot) }}">
            Cancel
        </x-button>
    </div>

    <x-card>
        <form action="{{ route('shoots.update', $shoot) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <x-input 
                        label="Shoot Title" 
                        name="title" 
                        :value="$shoot->title" 
                        required 
                    />
                </div>

                <div>
                    @php
                        $typesList = isset($shootTypes) && $shootTypes->isNotEmpty()
                            ? $shootTypes
                            : \App\Models\ShootType::where('is_active', true)->orderBy('name')->get();
                        $currentType = old('event_type', $shoot->event_type);
                        $hasCurrentTypeInList = false;
                    @endphp
                    <x-select label="Event Type" name="event_type" required>
                        @foreach($typesList as $type)
                            @if($currentType === $type->name)
                                @php $hasCurrentTypeInList = true; @endphp
                            @endif
                            <option value="{{ $type->name }}" @selected($currentType === $type->name)>{{ $type->name }}</option>
                        @endforeach
                        @if(!empty($currentType) && !$hasCurrentTypeInList)
                            <option value="{{ $currentType }}" selected>{{ $currentType }} (Custom)</option>
                        @endif
                    </x-select>
                    @if(auth()->user()?->isAdmin())
                        <div class="mt-1 flex items-center justify-between text-[11px]">
                            <span class="text-slate-400">Need another shoot type?</span>
                            <a href="{{ route('shoot-types.index') }}" class="text-[#4F46E5] hover:underline font-medium inline-flex items-center gap-1">
                                <x-icon name="plus" class="w-3 h-3" />
                                <span>Manage Shoot Types (Admin)</span>
                            </a>
                        </div>
                    @endif
                </div>

                <div>
                    <x-select label="Workflow Status" name="status" required>
                        @foreach(['scheduled', 'crew_assigned', 'in_progress', 'raw_uploaded', 'editing', 'client_review', 'completed', 'cancelled'] as $st)
                            <option value="{{ $st }}" @selected($shoot->status === $st)>{{ ucwords(str_replace('_', ' ', $st)) }}</option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <x-input 
                        label="Shoot Date" 
                        name="shoot_date" 
                        type="date" 
                        :value="$shoot->shoot_date->format('Y-m-d')" 
                        required 
                    />
                </div>

                <div>
                    <x-input 
                        label="Venue Name" 
                        name="venue" 
                        :value="$shoot->venue" 
                        required 
                    />
                </div>

                <div>
                    <x-input 
                        label="Agreed Total Amount (₹)" 
                        name="total_amount" 
                        type="number" 
                        step="0.01" 
                        :value="$shoot->total_amount" 
                        required 
                    />
                </div>

                <div>
                    <x-input 
                        label="Discount Amount (₹)" 
                        name="discount_amount" 
                        type="number" 
                        step="0.01" 
                        :value="$shoot->discount_amount" 
                    />
                </div>

                <div class="sm:col-span-2">
                    <x-input 
                        label="Google Maps Location URL" 
                        name="location_url" 
                        :value="$shoot->location_url" 
                    />
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Requirements / Shot List</label>
                    <textarea 
                        name="requirements" 
                        rows="2" 
                        class="w-full text-xs rounded-lg border border-slate-300 p-2.5 placeholder-slate-400 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                    >{{ old('requirements', $shoot->requirements) }}</textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <button form="delete-shoot-form" type="submit" class="text-xs text-rose-600 hover:text-rose-700 font-semibold cursor-pointer">
                    Delete Shoot
                </button>

                <div class="flex items-center gap-2">
                    <x-button variant="secondary" size="md" href="{{ route('shoots.show', $shoot) }}">
                        Cancel
                    </x-button>
                    <x-button variant="primary" size="md" type="submit" icon="check">
                        Save Changes
                    </x-button>
                </div>
            </div>
        </form>

        <form id="delete-shoot-form" action="{{ route('shoots.destroy', $shoot) }}" method="POST" onsubmit="return confirm('Delete this shoot record?')">
            @csrf
            @method('DELETE')
        </form>
    </x-card>
</div>
@endsection
