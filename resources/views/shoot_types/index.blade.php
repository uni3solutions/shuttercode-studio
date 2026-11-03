@extends('layouts.app', [
    'title' => 'Shoot & Event Types Management',
    'breadcrumbs' => ['Home' => '/', 'Settings' => route('settings.index'), 'Shoot Types' => route('shoot-types.index')]
])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-bold text-slate-900 tracking-tight">Shoot & Event Types</h2>
                <x-badge variant="primary" size="xs">Admin Only</x-badge>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Manage event categories (e.g. Baby Shoot, Maternity Shoot, Corporate Summit) available in enquiry forms and booking workflows</p>
        </div>
        <div class="flex items-center gap-2">
            <x-button variant="secondary" size="sm" icon="arrow-left" href="{{ route('settings.index') }}">
                <span>Studio Settings</span>
            </x-button>
            <x-button variant="secondary" size="sm" icon="message-square-quote" href="{{ route('leads.create') }}">
                <span>Test in Lead Form</span>
            </x-button>
        </div>
    </div>

    <!-- KPI Summary Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card 
            title="Total Shoot Types" 
            :value="$shootTypes->count() . ' Types'" 
            trend="Configured categories" 
            :trendUp="true" 
            icon="layers" 
            iconColor="indigo" 
            subtitle="Master catalog"
        />
        <x-stat-card 
            title="Active In Dropdowns" 
            :value="$shootTypes->where('is_active', true)->count() . ' Active'" 
            trend="Available for telecallers" 
            :trendUp="true" 
            icon="check-circle" 
            iconColor="emerald" 
            subtitle="Active selection"
        />
        <x-stat-card 
            title="Total Leads Tagged" 
            :value="$shootTypes->sum('leads_count') . ' Leads'" 
            trend="Across all event types" 
            :trendUp="true" 
            icon="user-plus" 
            iconColor="sky" 
            subtitle="Pipeline coverage"
        />
        <x-stat-card 
            title="Total Shoots Booked" 
            :value="$shootTypes->sum('shoots_count') . ' Shoots'" 
            trend="Completed & scheduled" 
            :trendUp="true" 
            icon="camera" 
            iconColor="amber" 
            subtitle="Studio production"
        />
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Create Shoot Type Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs p-5 sticky top-20">
                <div class="flex items-center gap-2.5 pb-4 mb-4 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-[#4F46E5]">
                        <x-icon name="plus" class="w-4 h-4" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Add New Shoot Type</h3>
                        <p class="text-[11px] text-slate-500">Will appear in Event Type dropdowns</p>
                    </div>
                </div>

                <form action="{{ route('shoot-types.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <x-input 
                            label="Shoot / Event Type Name" 
                            name="name" 
                            id="new_name" 
                            placeholder="e.g. Baby Shoot, Maternity Shoot" 
                            value="{{ old('name') }}" 
                            required 
                        />
                        <p class="text-[11px] text-slate-400 mt-1">Unique category name shown to telecallers and clients</p>
                    </div>

                    <div>
                        <label for="new_description" class="block text-xs font-semibold text-slate-700 mb-1">Description (Optional)</label>
                        <textarea 
                            name="description" 
                            id="new_description" 
                            rows="3" 
                            placeholder="e.g. Newborn portraits, infant milestones, cake smash, and toddler photo sessions."
                            class="w-full text-xs rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 placeholder-slate-400"
                        >{{ old('description') }}</textarea>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                        <div>
                            <span class="text-xs font-semibold text-slate-800 block">Status: Active</span>
                            <span class="text-[11px] text-slate-500 block">Enable immediately in dropdowns</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer" checked>
                            <div class="w-9 h-5 bg-slate-300 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#4F46E5]"></div>
                        </label>
                    </div>

                    <div class="pt-2">
                        <x-button type="submit" variant="primary" size="sm" icon="check" class="w-full justify-center">
                            <span>Save Shoot Type</span>
                        </x-button>
                    </div>
                </form>

                <!-- Quick Preset Recommendations -->
                <div class="mt-5 pt-4 border-t border-slate-100">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 block mb-2">Common Shoot Presets</span>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(['Baby Shoot', 'Maternity Shoot', 'Corporate Shoot', 'Pre-Wedding', 'Anniversary', 'Food & Culinary', 'Real Estate'] as $preset)
                            @if(!$shootTypes->contains('name', $preset))
                                <button 
                                    type="button" 
                                    onclick="document.getElementById('new_name').value = '{{ $preset }}'"
                                    class="text-[11px] px-2 py-1 rounded bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 transition-colors"
                                >
                                    + {{ $preset }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Shoot Types Table & Catalog -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Configured Shoot Categories</h3>
                        <p class="text-xs text-slate-500">Full list of active and archived shoot types</p>
                    </div>
                    <span class="text-xs text-slate-400 font-medium">{{ $shootTypes->count() }} total types</span>
                </div>

                @if($shootTypes->isEmpty())
                    <div class="p-8 text-center">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 text-[#4F46E5] flex items-center justify-center mx-auto mb-3">
                            <x-icon name="layers" class="w-6 h-6" />
                        </div>
                        <h4 class="text-sm font-bold text-slate-800">No Shoot Types Added Yet</h4>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Use the form on the left to add your first shoot type like Baby Shoot, Maternity, or Corporate Shoot.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                    <th class="py-3 px-4">Shoot Type</th>
                                    <th class="py-3 px-4">Description</th>
                                    <th class="py-3 px-4 text-center">Usage</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($shootTypes as $type)
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-7 h-7 rounded-lg bg-indigo-50 border border-indigo-100 text-[#4F46E5] flex items-center justify-center shrink-0">
                                                    <x-icon name="camera" class="w-3.5 h-3.5" />
                                                </div>
                                                <div>
                                                    <span class="font-bold text-slate-900 block">{{ $type->name }}</span>
                                                    <span class="text-[10px] text-slate-400 font-mono">{{ $type->slug }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 max-w-xs">
                                            <p class="text-slate-600 line-clamp-2">{{ $type->description ?: 'No description provided.' }}</p>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="inline-flex items-center gap-2">
                                                <span class="px-2 py-0.5 rounded bg-sky-50 text-sky-700 text-[11px] font-semibold border border-sky-200/60" title="{{ $type->leads_count }} leads tagged">
                                                    {{ $type->leads_count }} leads
                                                </span>
                                                <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[11px] font-semibold border border-indigo-200/60" title="{{ $type->shoots_count }} shoots booked">
                                                    {{ $type->shoots_count }} shoots
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <form action="{{ route('shoot-types.toggle', $type) }}" method="POST" class="inline">
                                                @csrf
                                                <button 
                                                    type="submit" 
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold transition-colors {{ $type->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200' }}"
                                                    title="Click to toggle active status"
                                                >
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $type->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                                    <span>{{ $type->is_active ? 'Active' : 'Inactive' }}</span>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <!-- Edit Trigger Button -->
                                                <button 
                                                    type="button" 
                                                    onclick="openEditModal({{ $type->id }}, '{{ addslashes($type->name) }}', '{{ addslashes($type->description ?? '') }}', {{ $type->is_active ? 'true' : 'false' }})"
                                                    class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-md transition-colors"
                                                    title="Edit Shoot Type"
                                                >
                                                    <x-icon name="edit-3" class="w-4 h-4" />
                                                </button>

                                                <!-- Delete Button -->
                                                <form action="{{ route('shoot-types.destroy', $type) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete or deactivate \'{{ addslashes($type->name) }}\'?');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button 
                                                        type="submit" 
                                                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-md transition-colors"
                                                        title="Delete / Deactivate Shoot Type"
                                                    >
                                                        <x-icon name="trash-2" class="w-4 h-4" />
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit Shoot Type Modal -->
<div id="edit-modal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-slate-200 max-w-md w-full shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-[#4F46E5]">
                    <x-icon name="edit-3" class="w-4 h-4" />
                </div>
                <h3 class="text-sm font-bold text-slate-900">Edit Shoot Type</h3>
            </div>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-md">
                <x-icon name="x" class="w-4 h-4" />
            </button>
        </div>

        <form id="edit-form" method="POST" class="p-5 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <x-input 
                    label="Shoot / Event Type Name" 
                    name="name" 
                    id="edit_name" 
                    required 
                />
            </div>

            <div>
                <label for="edit_description" class="block text-xs font-semibold text-slate-700 mb-1">Description (Optional)</label>
                <textarea 
                    name="description" 
                    id="edit_description" 
                    rows="3" 
                    class="w-full text-xs rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 placeholder-slate-400"
                ></textarea>
            </div>

            <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                <div>
                    <span class="text-xs font-semibold text-slate-800 block">Status: Active</span>
                    <span class="text-[11px] text-slate-500 block">Available in dropdown selection</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="sr-only peer">
                    <div class="w-9 h-5 bg-slate-300 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#4F46E5]"></div>
                </label>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                <x-button type="button" variant="secondary" size="sm" onclick="closeEditModal()">
                    <span>Cancel</span>
                </x-button>
                <x-button type="submit" variant="primary" size="sm" icon="check">
                    <span>Update Shoot Type</span>
                </x-button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, name, description, isActive) {
        const form = document.getElementById('edit-form');
        form.action = `/shoot-types/${id}`;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_description').value = description;
        document.getElementById('edit_is_active').checked = isActive;
        
        const modal = document.getElementById('edit-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditModal() {
        const modal = document.getElementById('edit-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endsection
