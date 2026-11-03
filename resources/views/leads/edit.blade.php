@extends('layouts.app', [
    'title' => 'Edit Lead: ' . $lead->lead_number,
    'breadcrumbs' => ['Home' => '/', 'Leads' => route('leads.index'), $lead->lead_number => route('leads.show', $lead), 'Edit' => '#']
])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Edit Enquiry #{{ $lead->lead_number }}</h2>
            <p class="text-xs text-slate-500 mt-0.5">Update contact details, budget, stage, and assigned telecaller</p>
        </div>
        <x-button variant="secondary" size="sm" icon="arrow-left" href="{{ route('leads.show', $lead) }}">
            Back to Enquiry
        </x-button>
    </div>

    <x-card>
        <form action="{{ route('leads.update', $lead) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input 
                        label="Prospect Full Name" 
                        name="customer_name" 
                        value="{{ old('customer_name', $lead->customer_name) }}"
                        required 
                        icon="user"
                    />
                </div>

                <div>
                    <x-input 
                        label="WhatsApp Phone Number" 
                        name="phone" 
                        value="{{ old('phone', $lead->phone) }}"
                        required 
                        icon="phone"
                    />
                </div>

                <div>
                    <x-input 
                        label="Email Address" 
                        name="email" 
                        type="email" 
                        value="{{ old('email', $lead->email) }}"
                        icon="mail"
                    />
                </div>

                <div>
                    <x-select label="Lead Source" name="source" required>
                        @foreach(['WhatsApp Meta (Ad / Direct)' => 'WhatsApp Meta', 'Website Form' => 'Website Form', 'Instagram DM' => 'Instagram DM', 'Walk-in' => 'Walk-in', 'Referral' => 'Referral'] as $label => $val)
                            <option value="{{ $val }}" {{ old('source', $lead->source) == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    @php
                        $typesList = isset($shootTypes) && $shootTypes->isNotEmpty()
                            ? $shootTypes
                            : \App\Models\ShootType::where('is_active', true)->orderBy('name')->get();
                        $currentType = old('event_type', $lead->event_type);
                        $hasCurrentTypeInList = false;
                    @endphp
                    <x-select label="Event Type" name="event_type" required>
                        <option value="">Select Event / Shoot Type...</option>
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
                    <x-input 
                        label="Expected Event Date" 
                        name="event_date" 
                        type="date" 
                        value="{{ old('event_date', $lead->event_date?->format('Y-m-d')) }}"
                    />
                </div>

                <div>
                    <x-input 
                        label="Estimated Budget (₹)" 
                        name="estimated_budget" 
                        type="number" 
                        step="0.01" 
                        value="{{ old('estimated_budget', $lead->estimated_budget) }}"
                    />
                </div>

                <div>
                    <x-input 
                        label="Event Venue / Location" 
                        name="venue" 
                        value="{{ old('venue', $lead->venue) }}"
                        icon="map-pin"
                    />
                </div>

                <div>
                    @if(auth()->user()?->isTelecaller())
                        <input type="hidden" name="assigned_to" value="{{ $lead->assigned_to ?? auth()->id() }}">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Assigned Telecaller</label>
                        <div class="relative">
                            <select disabled class="w-full text-xs rounded-lg border border-slate-200 bg-slate-100 text-slate-700 p-2.5 cursor-not-allowed font-medium appearance-none">
                                <option value="{{ $lead->assigned_to ?? auth()->id() }}" selected>{{ $lead->assignedUser?->name ?? auth()->user()->name }} (Assigned Telecaller)</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                <x-icon name="lock" class="w-3.5 h-3.5 text-slate-400" />
                            </div>
                        </div>
                    @else
                        <x-select label="Assigned Telecaller" name="assigned_to">
                            <option value="">Unassigned</option>
                            @foreach($telecallers as $t)
                                <option value="{{ $t->id }}" {{ old('assigned_to', $lead->assigned_to) == $t->id ? 'selected' : '' }}>
                                    {{ $t->name }} ({{ $t->role }})
                                </option>
                            @endforeach
                        </x-select>
                    @endif
                </div>

                <div>
                    <x-select label="Pipeline Status" name="status" required>
                        @foreach(\App\Models\Lead::statuses() as $val => $label)
                            <option value="{{ $val }}" {{ old('status', $lead->status) == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </x-select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Enquiry Notes / Requirements</label>
                    <textarea 
                        name="notes" 
                        rows="3" 
                        class="w-full text-xs rounded-lg border border-slate-300 p-3 placeholder-slate-400 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                    >{{ old('notes', $lead->notes) }}</textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <x-button variant="secondary" size="md" href="{{ route('leads.show', $lead) }}">
                    Cancel
                </x-button>
                <x-button variant="primary" size="md" type="submit" icon="check">
                    Update Enquiry
                </x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
