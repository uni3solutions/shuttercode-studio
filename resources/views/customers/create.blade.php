@extends('layouts.app', [
    'title' => 'New Customer Profile',
    'breadcrumbs' => ['Home' => '/', 'Customers' => route('customers.index'), 'New Customer' => '#']
])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Create Customer Profile</h2>
            <p class="text-xs text-slate-500 mt-0.5">Enter primary contact details for wedding or event shoots</p>
        </div>
        <x-button variant="secondary" size="sm" icon="arrow-left" href="{{ route('customers.index') }}">
            Back to Directory
        </x-button>
    </div>

    <x-card>
        <form action="{{ route('customers.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <x-input 
                        label="Full Customer / Couple Name" 
                        name="name" 
                        placeholder="e.g. Rohit & Sneha Sharma" 
                        required 
                        icon="user"
                    />
                </div>

                <div>
                    <x-input 
                        label="Primary Phone (WhatsApp enabled)" 
                        name="phone" 
                        placeholder="e.g. +91 98450 12345" 
                        required 
                        icon="phone"
                    />
                </div>

                <div>
                    <x-input 
                        label="Alternate / Family Phone" 
                        name="alternate_phone" 
                        placeholder="e.g. +91 98450 12346" 
                        icon="phone-call"
                    />
                </div>

                <div class="sm:col-span-2">
                    <x-input 
                        label="Email Address" 
                        name="email" 
                        type="email" 
                        placeholder="client@example.com" 
                        icon="mail"
                    />
                </div>

                <div class="sm:col-span-2">
                    <x-input 
                        label="City / Location" 
                        name="city" 
                        value="Bengaluru" 
                        placeholder="e.g. Bengaluru, Karnataka" 
                        icon="map-pin"
                    />
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Postal Address</label>
                    <textarea 
                        name="address" 
                        rows="2" 
                        placeholder="Residential address or billing address..."
                        class="w-full text-xs rounded-lg border border-slate-300 p-3 placeholder-slate-400 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                    >{{ old('address') }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Special Preferences / Notes</label>
                    <textarea 
                        name="notes" 
                        rows="2" 
                        placeholder="Specific photography preferences, reference styles, family contacts..."
                        class="w-full text-xs rounded-lg border border-slate-300 p-3 placeholder-slate-400 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                    >{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <x-button variant="secondary" size="md" href="{{ route('customers.index') }}">
                    Cancel
                </x-button>
                <x-button variant="primary" size="md" type="submit" icon="check">
                    Save Customer Profile
                </x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
