@extends('layouts.app', [
    'title' => 'Create Package',
    'breadcrumbs' => ['Home' => '/', 'Packages' => route('packages.index'), 'New Package' => '#']
])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Create Photography Package</h2>
            <p class="text-xs text-slate-500 mt-0.5">Define deliverables, duration, crew count, and deposit amount</p>
        </div>
        <x-button variant="secondary" size="sm" icon="arrow-left" href="{{ route('packages.index') }}">
            Back to Packages
        </x-button>
    </div>

    <x-card>
        <form action="{{ route('packages.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <x-input 
                        label="Package Name" 
                        name="name" 
                        placeholder="e.g. Royal Heritage Wedding" 
                        required 
                    />
                </div>

                <div>
                    <x-select label="Category" name="category" required>
                        <option value="Wedding">Wedding</option>
                        <option value="Pre-Wedding">Pre-Wedding</option>
                        <option value="Maternity">Maternity & Baby</option>
                        <option value="Corporate">Corporate & Events</option>
                        <option value="Birthday">Birthday & Celebration</option>
                        <option value="Fashion">Fashion & Portfolio</option>
                    </x-select>
                </div>

                <div>
                    @if(auth()->user()?->isAdmin())
                        <x-select label="Assigned Telecaller / Owner" name="created_by" required>
                            <option value="{{ auth()->id() }}">Self ({{ auth()->user()->name }})</option>
                            @foreach($telecallers as $tc)
                                @if($tc->id !== auth()->id())
                                    <option value="{{ $tc->id }}" @selected(old('created_by') == $tc->id)>
                                        {{ $tc->name }} ({{ ucfirst($tc->role) }})
                                    </option>
                                @endif
                            @endforeach
                        </x-select>
                    @else
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Package Creator / Telecaller</label>
                        <div class="px-3.5 py-2.5 rounded-lg border border-slate-200 bg-slate-50 text-xs text-slate-700 font-medium flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>{{ auth()->user()->name }} (Telecaller)</span>
                        </div>
                    @endif
                </div>

                <div>
                    <x-input 
                        label="Coverage Duration (Hours)" 
                        name="duration_hours" 
                        type="number" 
                        value="8" 
                        required 
                    />
                </div>

                <div>
                    <x-input 
                        label="Total Package Price (₹)" 
                        name="price" 
                        type="number" 
                        step="0.01" 
                        placeholder="e.g. 75000" 
                        required 
                    />
                </div>

                <div>
                    <x-input 
                        label="Advance Deposit Required (₹)" 
                        name="advance_amount" 
                        type="number" 
                        step="0.01" 
                        placeholder="e.g. 25000" 
                        required 
                    />
                </div>

                <div>
                    <x-input 
                        label="Number of Photographers" 
                        name="photographers_count" 
                        type="number" 
                        value="1" 
                        required 
                    />
                </div>

                <div>
                    <x-input 
                        label="Number of Videographers" 
                        name="videographers_count" 
                        type="number" 
                        value="1" 
                        required 
                    />
                </div>

                <div>
                    <x-input 
                        label="Printed Photo Albums" 
                        name="album_count" 
                        type="number" 
                        value="1" 
                        required 
                    />
                </div>

                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                        <input type="checkbox" name="drone_included" value="1" class="rounded border-slate-300 text-[#4F46E5] focus:ring-[#4F46E5]">
                        <span>Include 4K Drone Aerial Coverage</span>
                    </label>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Deliverables Checklist</label>
                    <textarea 
                        name="deliverables" 
                        rows="3" 
                        placeholder="• 2 Photographers&#10;• 1 Cinematic Video&#10;• 1 Lay-flat Album"
                        class="w-full text-xs rounded-lg border border-slate-300 p-3 placeholder-slate-400 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                    ></textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Description / Client Brochure Pitch</label>
                    <textarea 
                        name="description" 
                        rows="2" 
                        placeholder="Brief overview explaining why clients love this package..."
                        class="w-full text-xs rounded-lg border border-slate-300 p-3 placeholder-slate-400 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                    ></textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <x-button variant="secondary" size="md" href="{{ route('packages.index') }}">
                    Cancel
                </x-button>
                <x-button variant="primary" size="md" type="submit" icon="check">
                    Create Package
                </x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
