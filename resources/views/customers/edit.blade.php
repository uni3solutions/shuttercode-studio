@extends('layouts.app', [
    'title' => 'Edit Customer — ' . $customer->name,
    'breadcrumbs' => ['Home' => '/', 'Customers' => route('customers.index'), $customer->name => route('customers.show', $customer), 'Edit' => '#']
])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Edit Customer Profile</h2>
            <p class="text-xs text-slate-500 mt-0.5">Update contact info and preferences for {{ $customer->name }}</p>
        </div>
        <x-button variant="secondary" size="sm" icon="arrow-left" href="{{ route('customers.show', $customer) }}">
            Cancel
        </x-button>
    </div>

    <x-card>
        <form action="{{ route('customers.update', $customer) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <x-input 
                        label="Full Customer / Couple Name" 
                        name="name" 
                        :value="$customer->name"
                        required 
                        icon="user"
                    />
                </div>

                <div>
                    <x-input 
                        label="Primary Phone (WhatsApp enabled)" 
                        name="phone" 
                        :value="$customer->phone"
                        required 
                        icon="phone"
                    />
                </div>

                <div>
                    <x-input 
                        label="Alternate / Family Phone" 
                        name="alternate_phone" 
                        :value="$customer->alternate_phone"
                        icon="phone-call"
                    />
                </div>

                <div class="sm:col-span-2">
                    <x-input 
                        label="Email Address" 
                        name="email" 
                        type="email" 
                        :value="$customer->email"
                        icon="mail"
                    />
                </div>

                <div class="sm:col-span-2">
                    <x-input 
                        label="City / Location" 
                        name="city" 
                        :value="$customer->city"
                        icon="map-pin"
                    />
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Postal Address</label>
                    <textarea 
                        name="address" 
                        rows="2" 
                        class="w-full text-xs rounded-lg border border-slate-300 p-3 placeholder-slate-400 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                    >{{ old('address', $customer->address) }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Special Preferences / Notes</label>
                    <textarea 
                        name="notes" 
                        rows="2" 
                        class="w-full text-xs rounded-lg border border-slate-300 p-3 placeholder-slate-400 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                    >{{ old('notes', $customer->notes) }}</textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <button form="delete-customer-form" type="submit" class="text-xs text-rose-600 hover:text-rose-700 font-semibold cursor-pointer">
                    Delete Customer Profile
                </button>

                <div class="flex items-center gap-2">
                    <x-button variant="secondary" size="md" href="{{ route('customers.show', $customer) }}">
                        Cancel
                    </x-button>
                    <x-button variant="primary" size="md" type="submit" icon="check">
                        Save Changes
                    </x-button>
                </div>
            </div>
        </form>

        <form id="delete-customer-form" action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this customer?')">
            @csrf
            @method('DELETE')
        </form>
    </x-card>
</div>
@endsection
