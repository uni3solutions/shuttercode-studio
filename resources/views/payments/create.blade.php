@extends('layouts.app', [
    'title' => 'Record Payment',
    'breadcrumbs' => ['Home' => '/', 'Payments' => route('payments.index'), 'Record Payment' => '#']
])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Record Customer Transaction</h2>
            <p class="text-xs text-slate-500 mt-0.5">Post verified incoming payment via UPI, Bank Transfer, or Cash</p>
        </div>
        <x-button variant="secondary" size="sm" icon="arrow-left" href="{{ route('payments.index') }}">
            Back to Payments
        </x-button>
    </div>

    <x-card>
        <form action="{{ route('payments.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Select Shoot Booking <span class="text-rose-500">*</span></label>
                    <select 
                        name="shoot_id" 
                        id="shoot_select" 
                        required 
                        onchange="updateShootBalance(this)"
                        class="w-full text-xs rounded-lg border border-slate-300 py-2.5 px-3 bg-white text-slate-800 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                    >
                        <option value="">-- Choose active shoot with pending dues --</option>
                        @foreach($shoots as $shoot)
                            <option 
                                value="{{ $shoot->id }}" 
                                data-balance="{{ $shoot->balance_amount }}"
                                data-total="{{ $shoot->total_amount }}"
                                data-customer="{{ $shoot->customer?->name }}"
                                {{ (old('shoot_id') == $shoot->id || ($selectedShootId ?? null) == $shoot->id) ? 'selected' : '' }}
                            >
                                {{ $shoot->shoot_number }} — {{ $shoot->title }} ({{ $shoot->customer?->name }}) — Due: ₹{{ number_format($shoot->balance_amount) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Live balance callout box -->
                <div id="balance_callout" class="p-3.5 rounded-xl bg-indigo-50/60 border border-indigo-100 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-semibold text-indigo-900 uppercase tracking-wider block">Outstanding Balance</span>
                        <span id="callout_customer" class="text-xs text-slate-500 font-medium">Select a booking above</span>
                    </div>
                    <div class="text-right">
                        <span id="callout_balance" class="text-base font-bold text-indigo-700">₹0</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input 
                            label="Payment Amount (₹)" 
                            name="amount" 
                            id="amount_input"
                            type="number" 
                            step="0.01" 
                            placeholder="e.g. 50000" 
                            required 
                            icon="indian-rupee"
                        />
                    </div>

                    <div>
                        <x-select label="Payment Method" name="payment_method" required>
                            <option value="upi">UPI (GPay / PhonePe / Paytm)</option>
                            <option value="bank_transfer">Bank Transfer (NEFT / IMPS)</option>
                            <option value="cash">Cash Deposit</option>
                            <option value="card">Credit / Debit Card</option>
                            <option value="cheque">Cheque</option>
                        </x-select>
                    </div>

                    <div>
                        <x-select label="Payment Type / Stage" name="payment_type" required>
                            <option value="advance">Advance Booking Deposit</option>
                            <option value="milestone">Shoot Day Milestone</option>
                            <option value="final">Final Settlement</option>
                            <option value="extra">Additional Deliverables</option>
                        </x-select>
                    </div>

                    <div>
                        <x-input 
                            label="Transaction Date" 
                            name="paid_at" 
                            type="date" 
                            value="{{ date('Y-m-d') }}" 
                            required 
                        />
                    </div>

                    <div class="sm:col-span-2">
                        <x-input 
                            label="Bank / UPI UTR Reference Number" 
                            name="transaction_reference" 
                            placeholder="e.g. UTR #412389103982 or Cheque #000124" 
                            icon="hash"
                        />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Internal Payment Notes</label>
                        <textarea 
                            name="notes" 
                            rows="2" 
                            placeholder="e.g. Received via GPay from groom's father" 
                            class="w-full text-xs rounded-lg border border-slate-300 p-3 placeholder-slate-400 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                        ></textarea>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <x-button variant="secondary" size="md" href="{{ route('payments.index') }}">
                    Cancel
                </x-button>
                <x-button variant="primary" size="md" type="submit" icon="check-circle">
                    Confirm & Record Payment
                </x-button>
            </div>
        </form>
    </x-card>
</div>

<script>
    function updateShootBalance(select) {
        const option = select.options[select.selectedIndex];
        if (option && option.value) {
            const balance = option.getAttribute('data-balance');
            const customer = option.getAttribute('data-customer');
            document.getElementById('callout_customer').textContent = 'Client: ' + customer;
            document.getElementById('callout_balance').textContent = '₹' + Number(balance).toLocaleString('en-IN');
            if (!document.getElementById('amount_input').value) {
                document.getElementById('amount_input').value = balance;
            }
        } else {
            document.getElementById('callout_customer').textContent = 'Select a booking above';
            document.getElementById('callout_balance').textContent = '₹0';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const sel = document.getElementById('shoot_select');
        if (sel && sel.value) {
            updateShootBalance(sel);
        }
    });
</script>
@endsection
