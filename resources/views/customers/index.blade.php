@extends('layouts.app', [
    'title' => 'Customer Directory',
    'breadcrumbs' => ['Home' => '/', 'Directory' => '#', 'Customers' => route('customers.index')]
])

@section('content')
<div class="space-y-5">
    <!-- Header Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Customer Management</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage customer contact profiles, booking history, and invoices</p>
        </div>
        <div class="flex items-center gap-2.5">
            <x-button variant="primary" size="sm" icon="user-plus" href="{{ route('customers.create') }}">
                <span>New Customer</span>
            </x-button>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <x-card :noPadding="true">
        <form method="GET" action="{{ route('customers.index') }}" class="p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
            <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <x-icon name="search" class="w-4 h-4" />
                </div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search by client name, phone, or email..." 
                    class="w-full pl-9 pr-3.5 py-1.5 text-xs bg-white rounded-lg border border-slate-200 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                />
            </div>
            <div class="flex items-center gap-2">
                <x-button variant="primary" size="sm" type="submit" icon="search">
                    Search
                </x-button>
                @if(request('search'))
                    <x-button variant="secondary" size="sm" href="{{ route('customers.index') }}">
                        Clear
                    </x-button>
                @endif
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4 first:pl-5">Client Name</th>
                        <th class="py-3 px-4">Contact Phone</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">Location / City</th>
                        <th class="py-3 px-4">Shoots</th>
                        <th class="py-3 px-4">Total Paid</th>
                        <th class="py-3 px-4 text-right pr-5">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4 first:pl-5">
                                <a href="{{ route('customers.show', $customer) }}" class="font-bold text-slate-900 hover:text-[#4F46E5] flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-[11px]">
                                        {{ substr($customer->name, 0, 1) }}
                                    </div>
                                    <span>{{ $customer->name }}</span>
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono text-slate-700 font-medium">{{ $customer->phone }}</span>
                                    @if($customer->phone)
                                        @php
                                            $cleanPhone = preg_replace('/[^0-9]/', '', $customer->phone);
                                            if (strlen($cleanPhone) === 10) $cleanPhone = '91' . $cleanPhone;
                                        @endphp
                                        <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" title="WhatsApp Customer" class="text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 p-0.5 rounded transition-colors inline-flex items-center">
                                            <x-icon name="message-circle" class="w-3.5 h-3.5" />
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-slate-500">{{ $customer->email ?? '—' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-slate-700 font-medium">{{ $customer->city ?? 'Bengaluru' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">
                                    {{ $customer->shoots_count }} Bookings
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-900">₹{{ number_format($customer->total_spent) }}</span>
                            </td>
                            <td class="py-3 px-4 text-right pr-5 whitespace-nowrap">
                                <div class="inline-flex items-center gap-1 justify-end">
                                    <a href="{{ route('customers.show', $customer) }}" title="View Customer Profile" class="w-7 h-7 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-[#4F46E5] hover:bg-indigo-50/80 transition-colors">
                                        <x-icon name="eye" class="w-4 h-4" />
                                    </a>
                                    <a href="{{ route('customers.edit', $customer) }}" title="Edit Customer" class="w-7 h-7 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors">
                                        <x-icon name="pencil" class="w-3.5 h-3.5" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <x-icon name="users" class="w-8 h-8 text-slate-300" />
                                    <p class="text-sm font-medium text-slate-600">No customers found</p>
                                    <p class="text-xs text-slate-400">Add a new customer to start recording bookings.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">
                {{ $customers->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
