@extends('layouts.app', [
    'title' => $lead->customer_name . ' (' . $lead->lead_number . ')',
    'breadcrumbs' => ['Home' => '/', 'Leads' => route('leads.index'), $lead->lead_number => '#']
])

@section('content')
<div class="space-y-6">
    <!-- Header Summary Card -->
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 border border-indigo-100 text-[#4F46E5] font-bold text-xl flex items-center justify-center shrink-0">
                <x-icon name="message-square-quote" class="w-7 h-7" />
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">{{ $lead->lead_number }}</span>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">{{ $lead->customer_name }}</h2>
                    <x-badge :variant="$lead->status_variant" :dot="true">
                        {{ $lead->status_label }}
                    </x-badge>
                </div>
                <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-slate-500 mt-1">
                    <span>{{ $lead->event_type }}</span>
                    <span>&bull;</span>
                    <span>Est. Budget: ₹{{ number_format($lead->estimated_budget) }}</span>
                    <span>&bull;</span>
                    <span>Source: {{ $lead->source }}</span>
                    <span>&bull;</span>
                    <span>Created By: <strong class="text-slate-700">{{ $lead->creator?->name ?? 'System' }}</strong></span>
                    <span>&bull;</span>
                    <span>Assigned: {{ $lead->assignedUser?->name ?? 'Unassigned' }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ $lead->whatsAppUrl }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors">
                <x-icon name="message-circle" class="w-4 h-4 text-emerald-600" />
                <span>Open WhatsApp</span>
            </a>

            @if($lead->status !== 'converted')
                <form action="{{ route('leads.convert', $lead) }}" method="POST">
                    @csrf
                    <x-button variant="primary" size="sm" type="submit" icon="user-check">
                        Convert to Customer
                    </x-button>
                </form>
            @else
                <x-badge variant="success" size="md">Customer Created</x-badge>
            @endif
        </div>
    </div>

    <!-- Main Two-Column View -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Activity Timeline -->
        <div class="lg:col-span-2 space-y-6">
            <x-card title="Communication Timeline & Follow-ups" subtitle="Calls, WhatsApp messages, and notes">
                <div class="space-y-4">
                    @forelse($lead->activities as $act)
                        <div class="flex items-start gap-3 p-3.5 rounded-lg border border-slate-100 hover:bg-slate-50 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 shrink-0 mt-0.5">
                                @if($act->type === 'call')
                                    <x-icon name="phone-call" class="w-4 h-4 text-blue-600" />
                                @elseif($act->type === 'whatsapp')
                                    <x-icon name="message-circle" class="w-4 h-4 text-emerald-600" />
                                @elseif($act->type === 'meeting')
                                    <x-icon name="calendar" class="w-4 h-4 text-purple-600" />
                                @else
                                    <x-icon name="file-text" class="w-4 h-4 text-slate-600" />
                                @endif
                            </div>
                            <div class="flex-1 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-900 capitalize">{{ $act->type }} &bull; {{ $act->user?->name ?? 'Staff' }}</span>
                                    <span class="text-slate-400">{{ $act->created_at->format('d M, h:i A') }}</span>
                                </div>
                                <p class="text-slate-700 mt-1 whitespace-pre-line">{{ $act->details }}</p>
                                @if($act->next_follow_up_at)
                                    <div class="mt-2 inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                        <x-icon name="clock" class="w-3 h-3" />
                                        <span>Next follow-up: {{ $act->next_follow_up_at->format('d M Y, h:i A') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic">No activities logged yet.</p>
                    @endforelse
                </div>
            </x-card>
        </div>

        <!-- Right: Log Follow-up Form -->
        <div class="space-y-6">
            <x-card title="Record Activity" subtitle="Update lead follow-up status">
                <form action="{{ route('leads.activities.store', $lead) }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <x-select label="Activity Type" name="type" required>
                            <option value="whatsapp">WhatsApp Message Sent</option>
                            <option value="call">Phone Call</option>
                            <option value="meeting">Studio Consultation</option>
                            <option value="note">Internal Note</option>
                        </x-select>
                    </div>

                    <div>
                        <x-select label="Update Pipeline Status" name="status">
                            <option value="">Keep Current ({{ $lead->status_label }})</option>
                            @foreach(\App\Models\Lead::statuses() as $val => $label)
                                @if($val !== $lead->status)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endif
                            @endforeach
                        </x-select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Follow-up Notes</label>
                        <textarea 
                            name="details" 
                            rows="3" 
                            required 
                            placeholder="Summary of conversation, packages discussed, or customer concerns..."
                            class="w-full text-xs rounded-lg border border-slate-300 p-2.5 placeholder-slate-400 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                        ></textarea>
                    </div>

                    <div>
                        <x-input 
                            label="Next Follow-up Reminder" 
                            name="next_follow_up_at" 
                            type="datetime-local" 
                        />
                    </div>

                    <x-button 
                        variant="primary" 
                        size="md" 
                        type="submit" 
                        icon="check" 
                        class="w-full justify-center"
                    >
                        Save Activity Note
                    </x-button>
                </form>
            </x-card>

            <!-- Prospect Details Card -->
            <x-card title="Prospect Details">
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-400">Created By:</span>
                        <span class="font-medium text-slate-800">{{ $lead->creator?->name ?? 'System / Direct Enquiry' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-400">Phone:</span>
                        <span class="font-medium text-slate-800">{{ $lead->phone }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-400">Email:</span>
                        <span class="font-medium text-slate-800">{{ $lead->email ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-400">Event Date:</span>
                        <span class="font-medium text-slate-800">{{ $lead->event_date ? $lead->event_date->format('d M Y') : 'Tentative' }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-400">Venue:</span>
                        <span class="font-medium text-slate-800">{{ $lead->venue ?? 'Not Specified' }}</span>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection
