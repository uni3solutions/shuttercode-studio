@extends('layouts.app', [
    'title' => 'Generate Tax Invoice',
    'breadcrumbs' => ['Home' => '/', 'Invoices' => route('invoices.index'), 'New Invoice' => '#']
])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Generate Tax Invoice</h2>
            <p class="text-xs text-slate-500 mt-0.5">Issue formal GST tax invoice with payment verification</p>
        </div>
        <x-button variant="secondary" size="sm" icon="arrow-left" href="{{ route('invoices.index') }}">
            Back to Invoices
        </x-button>
    </div>

    @if(isset($telecaller) && $telecaller)
        <div class="p-3.5 rounded-xl bg-indigo-50/80 border border-indigo-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 text-[#4F46E5] flex items-center justify-center shrink-0">
                    <x-icon name="user-check" class="w-4 h-4" />
                </div>
                <div>
                    <span class="font-bold text-slate-900 block">Lead Telecaller: {{ $telecaller->name }}</span>
                    <span class="text-[11px] text-slate-500">Invoice will generate using <strong>{{ $telecaller->name }}'s</strong> invoice settings (Prefix: <strong class="font-mono text-slate-700">{{ $settings->invoice_prefix }}</strong>, Signatory: <strong>{{ $settings->authorized_signatory_name }}</strong>, UPI: <strong>{{ $settings->upi_id }}</strong>)</span>
                </div>
            </div>
            @if(auth()->user()?->isAdmin() || auth()->id() === $telecaller->id)
                <a href="{{ route('settings.index', auth()->user()?->isAdmin() ? ['telecaller_id' => $telecaller->id] : []) }}" class="text-[#4F46E5] hover:underline font-semibold text-[11px] shrink-0 inline-flex items-center gap-1">
                    <span>Edit Telecaller Settings</span>
                    <x-icon name="arrow-right" class="w-3 h-3" />
                </a>
            @endif
        </div>
    @else
        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between text-xs">
            <div class="flex items-center gap-2">
                <x-icon name="info" class="w-4 h-4 text-slate-400" />
                <span class="text-slate-600">Generating invoice using <strong>Studio Master Settings</strong> (Prefix: <strong class="font-mono">{{ $settings->invoice_prefix }}</strong>).</span>
            </div>
        </div>
    @endif

    <x-card>
        <form action="{{ route('invoices.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <x-select label="Select Shoot Booking" name="shoot_id" id="shoot_selector" onchange="if(this.value){ window.location.href='{{ route('invoices.create') }}?shoot_id=' + this.value; }" required>
                        <option value="">-- Choose Shoot --</option>
                        @foreach($shoots as $sh)
                            <option value="{{ $sh->id }}" @selected($selectedShootId == $sh->id) data-amount="{{ $sh->total_amount }}" data-discount="{{ $sh->discount_amount }}">
                                {{ $sh->shoot_number }} — {{ $sh->title }} ({{ $sh->customer?->name }})
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <x-input 
                        label="Subtotal Amount (₹)" 
                        name="subtotal" 
                        type="number" 
                        step="0.01" 
                        placeholder="e.g. 150000" 
                        required 
                    />
                </div>

                <div>
                    <x-input 
                        label="Discount Amount (₹)" 
                        name="discount_amount" 
                        type="number" 
                        step="0.01" 
                        value="0" 
                    />
                </div>

                <div>
                    <x-select label="GST Tax Rate (%)" name="tax_rate" required>
                        <option value="18.00" @selected(($settings->default_tax_rate ?? 18) == 18)>18% GST (9% CGST + 9% SGST)</option>
                        <option value="12.00" @selected(($settings->default_tax_rate ?? 18) == 12)>12% GST (6% CGST + 6% SGST)</option>
                        <option value="5.00" @selected(($settings->default_tax_rate ?? 18) == 5)>5% GST (2.5% CGST + 2.5% SGST)</option>
                        <option value="0.00" @selected(($settings->default_tax_rate ?? 18) == 0)>0% (Nil / Exempt)</option>
                    </x-select>
                </div>

                <div>
                    <x-input 
                        label="Issue Date" 
                        name="issue_date" 
                        type="date" 
                        value="{{ date('Y-m-d') }}" 
                        required 
                    />
                </div>

                <div>
                    <x-input 
                        label="Due Date" 
                        name="due_date" 
                        type="date" 
                        value="{{ date('Y-m-d', strtotime('+15 days')) }}" 
                        required 
                    />
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Payment Terms & Instructions</label>
                    <textarea 
                        name="terms" 
                        rows="2" 
                        class="w-full text-xs rounded-lg border border-slate-300 p-2.5 placeholder-slate-400 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                    >{{ $settings->invoice_terms ?? 'Payment due within 15 days of issue date. RAW files delivered upon full settlement.' }}</textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <x-button variant="secondary" size="md" href="{{ route('invoices.index') }}">
                    Cancel
                </x-button>
                <x-button variant="primary" size="md" type="submit" icon="check">
                    Generate Invoice
                </x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
