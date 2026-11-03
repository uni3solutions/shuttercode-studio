@extends('layouts.app', [
    'title' => 'Edit Package — ' . $package->name,
    'breadcrumbs' => ['Home' => '/', 'Packages' => route('packages.index'), $package->name => '#', 'Edit' => '#']
])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Edit Photography Package</h2>
            <p class="text-xs text-slate-500 mt-0.5">Update pricing, deliverables, and specs for {{ $package->name }}</p>
        </div>
        <x-button variant="secondary" size="sm" icon="arrow-left" href="{{ route('packages.index') }}">
            Back to Packages
        </x-button>
    </div>

    <x-card>
        <form action="{{ route('packages.update', $package) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <x-input 
                        label="Package Name" 
                        name="name" 
                        :value="$package->name" 
                        required 
                    />
                </div>

                <div>
                    <x-select label="Category" name="category" required>
                        @foreach(['Wedding', 'Pre-Wedding', 'Maternity', 'Corporate', 'Birthday', 'Fashion'] as $cat)
                            <option value="{{ $cat }}" @selected($package->category === $cat)>{{ $cat }}</option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    @if(auth()->user()?->isAdmin())
                        <x-select label="Assigned Telecaller / Owner" name="created_by" required>
                            @foreach($telecallers as $tc)
                                <option value="{{ $tc->id }}" @selected(old('created_by', $package->created_by) == $tc->id)>
                                    {{ $tc->name }} ({{ ucfirst($tc->role) }})
                                </option>
                            @endforeach
                        </x-select>
                    @else
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Package Telecaller / Owner</label>
                        <div class="px-3.5 py-2.5 rounded-lg border border-slate-200 bg-slate-50 text-xs text-slate-700 font-medium flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            <span>{{ $package->creator?->name ?? 'Studio Standard' }} ({{ $package->creator?->role ?? 'Admin' }})</span>
                        </div>
                    @endif
                </div>

                <div>
                    <x-input 
                        label="Coverage Duration (Hours)" 
                        name="duration_hours" 
                        type="number" 
                        :value="$package->duration_hours" 
                        required 
                    />
                </div>

                <div>
                    <x-input 
                        label="Total Package Price (₹)" 
                        name="price" 
                        type="number" 
                        step="0.01" 
                        :value="$package->price" 
                        required 
                    />
                </div>

                <div>
                    <x-input 
                        label="Advance Deposit Required (₹)" 
                        name="advance_amount" 
                        type="number" 
                        step="0.01" 
                        :value="$package->advance_amount" 
                        required 
                    />
                </div>

                <div>
                    <x-input 
                        label="Number of Photographers" 
                        name="photographers_count" 
                        type="number" 
                        :value="$package->photographers_count" 
                        required 
                    />
                </div>

                <div>
                    <x-input 
                        label="Number of Videographers" 
                        name="videographers_count" 
                        type="number" 
                        :value="$package->videographers_count" 
                        required 
                    />
                </div>

                <div>
                    <x-input 
                        label="Printed Photo Albums" 
                        name="album_count" 
                        type="number" 
                        :value="$package->album_count" 
                        required 
                    />
                </div>

                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                        <input type="checkbox" name="drone_included" value="1" @checked($package->drone_included) class="rounded border-slate-300 text-[#4F46E5] focus:ring-[#4F46E5]">
                        <span>Include 4K Drone Aerial Coverage</span>
                    </label>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Deliverables Checklist</label>
                    <textarea 
                        name="deliverables" 
                        rows="3" 
                        class="w-full text-xs rounded-lg border border-slate-300 p-3 placeholder-slate-400 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                    >{{ old('deliverables', $package->deliverables) }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Description</label>
                    <textarea 
                        name="description" 
                        rows="2" 
                        class="w-full text-xs rounded-lg border border-slate-300 p-3 placeholder-slate-400 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                    >{{ old('description', $package->description) }}</textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <button form="delete-package-form" type="submit" class="text-xs text-rose-600 hover:text-rose-700 font-semibold cursor-pointer">
                    Delete Package
                </button>

                <div class="flex items-center gap-2">
                    <x-button variant="secondary" size="md" href="{{ route('packages.index') }}">
                        Cancel
                    </x-button>
                    <x-button variant="primary" size="md" type="submit" icon="check">
                        Save Changes
                    </x-button>
                </div>
            </div>
        </form>

        <form id="delete-package-form" action="{{ route('packages.destroy', $package) }}" method="POST" onsubmit="return confirm('Delete this package?')">
            @csrf
            @method('DELETE')
        </form>
    </x-card>
</div>
@endsection
