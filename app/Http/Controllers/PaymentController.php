<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Payment;
use App\Models\Shoot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Payment::with(['shoot.invoices', 'customer', 'recordedBy', 'invoice']);

        if ($request->filled('method')) {
            $query->where('payment_method', $request->input('method'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                    ->orWhere('transaction_reference', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $payments = $query->orderBy('paid_at', 'desc')->paginate(15)->withQueryString();
        $shoots = Shoot::where('balance_amount', '>', 0)->get();

        // Total Verified Collections
        $totalCollected = Payment::where('status', 'verified')->sum('amount');
        $totalOutstanding = Shoot::whereNotIn('status', ['completed', 'cancelled'])->sum('balance_amount');

        return view('payments.index', compact('payments', 'shoots', 'totalCollected', 'totalOutstanding'));
    }

    public function create(Request $request): View
    {
        $shoots = Shoot::with('customer')->where('balance_amount', '>', 0)->get();
        $selectedShootId = $request->query('shoot_id');

        return view('payments.create', compact('shoots', 'selectedShootId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shoot_id' => ['required', 'exists:shoots,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string'],
            'payment_type' => ['required', 'string'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $shoot = Shoot::findOrFail($validated['shoot_id']);
        $validated['customer_id'] = $shoot->customer_id;
        $validated['status'] = 'verified';
        $validated['recorded_by'] = auth()->id() ?? 1;

        // Ensure shoot has a tax invoice
        $invoice = $shoot->ensureInvoice();
        $validated['invoice_id'] = $invoice->id;

        $count = Payment::whereYear('created_at', date('Y'))->count() + 1;
        $validated['payment_number'] = 'PAY-'.date('Y').'-'.str_pad($count, 3, '0', STR_PAD_LEFT);

        $payment = Payment::create($validated);

        ActivityLog::log('payment_recorded', "Payment {$payment->payment_number} of ₹".number_format($payment->amount)." recorded for shoot {$shoot->shoot_number} under invoice {$invoice->invoice_number}.", $shoot);

        $shoot->refresh();
        $statusMsg = $shoot->balance_amount <= 0
            ? "Payment {$payment->payment_number} of ₹".number_format($payment->amount)." recorded under Invoice {$invoice->invoice_number}! Shoot is now Fully Paid."
            : "Payment {$payment->payment_number} of ₹".number_format($payment->amount)." recorded under Invoice {$invoice->invoice_number}! Remaining balance: ₹".number_format($shoot->balance_amount).'.';

        return back()->with('success', $statusMsg);
    }
}
