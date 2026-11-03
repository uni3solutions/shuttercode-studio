@extends('layouts.app', [
    'title' => 'New Lead Enquiry',
    'breadcrumbs' => ['Home' => '/', 'Leads' => route('leads.index'), 'New Enquiry' => '#']
])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Record New Enquiry</h2>
            <p class="text-xs text-slate-500 mt-0.5">Log incoming phone calls, WhatsApp inquiries, or website form submissions</p>
        </div>
        <x-button variant="secondary" size="sm" icon="arrow-left" href="{{ route('leads.index') }}">
            Back to Pipeline
        </x-button>
    </div>

    <x-card>
        <form action="{{ route('leads.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input 
                        label="Prospect Full Name" 
                        name="customer_name" 
                        placeholder="e.g. Priya Sen" 
                        required 
                        icon="user"
                    />
                </div>

                <div>
                    <x-input 
                        label="WhatsApp Phone Number" 
                        name="phone" 
                        placeholder="e.g. +91 91234 56789" 
                        required 
                        icon="phone"
                    />
                </div>

                <div>
                    <x-input 
                        label="Email Address" 
                        name="email" 
                        type="email" 
                        placeholder="prospect@gmail.com" 
                        icon="mail"
                    />
                </div>

                <div>
                    <x-select label="Lead Source" name="source" required>
                        <option value="WhatsApp Meta">WhatsApp Meta (Ad / Direct)</option>
                        <option value="Website Form">Website Form</option>
                        <option value="Instagram DM">Instagram DM</option>
                        <option value="Walk-in">Walk-in Visit</option>
                        <option value="Referral">Client Referral</option>
                    </x-select>
                </div>

                <div>
                    @php
                        $typesList = isset($shootTypes) && $shootTypes->isNotEmpty()
                            ? $shootTypes
                            : \App\Models\ShootType::where('is_active', true)->orderBy('name')->get();
                    @endphp
                    <x-select label="Event Type" name="event_type" required>
                        <option value="">Select Event / Shoot Type...</option>
                        @foreach($typesList as $type)
                            <option value="{{ $type->name }}" @selected(old('event_type') === $type->name)>{{ $type->name }}</option>
                        @endforeach
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
                    />
                </div>

                <div>
                    <x-input 
                        label="Estimated Budget (₹)" 
                        name="estimated_budget" 
                        type="number" 
                        step="0.01" 
                        placeholder="e.g. 150000" 
                    />
                </div>

                <div>
                    <x-input 
                        label="Event Venue / Location" 
                        name="venue" 
                        placeholder="e.g. Goa, Leela Palace, etc." 
                        icon="map-pin"
                    />
                </div>

                <div>
                    @if(auth()->user()?->isTelecaller())
                        <input type="hidden" name="assigned_to" value="{{ auth()->id() }}">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Assigned Telecaller</label>
                        <div class="relative">
                            <select disabled class="w-full text-xs rounded-lg border border-slate-200 bg-slate-100 text-slate-700 p-2.5 cursor-not-allowed font-medium appearance-none">
                                <option value="{{ auth()->id() }}" selected>{{ auth()->user()->name }} (You - Telecaller)</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                <x-icon name="lock" class="w-3.5 h-3.5 text-slate-400" />
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Automatically assigned to you as the creator.</p>
                    @else
                        <x-select label="Assigned Telecaller" name="assigned_to">
                            <option value="">Unassigned</option>
                            @foreach($telecallers as $t)
                                <option value="{{ $t->id }}" @selected(old('assigned_to') == $t->id)>{{ $t->name }} ({{ $t->role }})</option>
                            @endforeach
                        </x-select>
                    @endif
                </div>

                <div>
                    <x-select label="Initial Pipeline Status" name="status" required>
                        <option value="new">New Enquiry</option>
                        <option value="contacted">Contacted</option>
                        <option value="in_discussion">In Discussion</option>
                        <option value="interested">Interested</option>
                        <option value="follow_up_later">Follow Up Later</option>
                        <option value="not_responding">Not Responding</option>
                        <option value="not_interested">Not Interested</option>
                    </x-select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Initial Enquiry Notes / Requirements</label>
                    <textarea 
                        name="notes" 
                        rows="3" 
                        placeholder="Details discussed during first contact..."
                        class="w-full text-xs rounded-lg border border-slate-300 p-3 placeholder-slate-400 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                    ></textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <x-button variant="secondary" size="md" href="{{ route('leads.index') }}">
                    Cancel
                </x-button>
                <x-button variant="primary" size="md" type="submit" icon="check">
                    Save Enquiry
                </x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
