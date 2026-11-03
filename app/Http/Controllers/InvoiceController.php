<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Shoot;
use App\Models\StudioSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $query = Invoice::with(['shoot', 'customer', 'telecaller', 'lead'])
            ->orderBy('issue_date', 'desc');

        if ($user?->isTelecaller()) {
            $query->where(function ($q) use ($user) {
                $q->where('telecaller_id', $user->id)
                    ->orWhereHas('shoot', function ($sq) use ($user) {
                        $sq->where('created_by', $user->id);
                    })
                    ->orWhereHas('lead', function ($lq) use ($user) {
                        $lq->where('assigned_to', $user->id)->orWhere('created_by', $user->id);
                    });
            });
        }

        $invoices = $query->paginate(15);

        return view('invoices.index', compact('invoices'));
    }

    public function create(Request $request): View
    {
        $user = auth()->user();
        $query = Shoot::with(['customer', 'package', 'lead'])->orderBy('shoot_date', 'desc');

        if ($user?->isTelecaller()) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhereHas('lead', function ($lq) use ($user) {
                        $lq->where('assigned_to', $user->id)->orWhere('created_by', $user->id);
                    });
            });
        }

        $shoots = $query->get();
        $selectedShootId = $request->query('shoot_id');
        $selectedShoot = $selectedShootId ? Shoot::with(['customer.lead', 'lead'])->find($selectedShootId) : null;

        $telecaller = $selectedShoot?->getTelecaller() ?? ($user?->isTelecaller() ? $user : null);
        $settings = $telecaller ? StudioSetting::forUser($telecaller) : StudioSetting::current();

        return view('invoices.create', compact('shoots', 'selectedShootId', 'settings', 'telecaller'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shoot_id' => ['required', 'exists:shoots,id'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'terms' => ['nullable', 'string'],
        ]);

        $shoot = Shoot::with(['customer.lead', 'lead'])->findOrFail($validated['shoot_id']);
        $validated['customer_id'] = $shoot->customer_id;

        // Resolve telecaller based on lead or shoot
        $telecaller = $shoot->getTelecaller() ?? (auth()->user()?->isTelecaller() ? auth()->user() : null);
        $lead = $shoot->lead ?? $shoot->customer?->lead ?? ($shoot->customer?->phone ? Lead::where('phone', $shoot->customer->phone)->latest()->first() : null);

        $validated['lead_id'] = $lead?->id;
        $validated['telecaller_id'] = $telecaller?->id;

        $subtotal = $validated['subtotal'];
        $discount = $validated['discount_amount'] ?? 0;
        $taxRate = $validated['tax_rate'];
        $taxAmount = round(($subtotal - $discount) * ($taxRate / 100), 2);
        $totalAmount = ($subtotal - $discount) + $taxAmount;

        $validated['tax_amount'] = $taxAmount;
        $validated['total_amount'] = $totalAmount;
        $validated['paid_amount'] = $shoot->paid_amount;
        $validated['balance_amount'] = max(0, $totalAmount - $shoot->paid_amount);
        $validated['status'] = $validated['balance_amount'] <= 0 ? 'paid' : ($validated['paid_amount'] > 0 ? 'partially_paid' : 'sent');

        // Generate invoice number using that telecaller's invoice prefix
        $settings = $telecaller ? StudioSetting::forUser($telecaller) : StudioSetting::current();
        $prefix = $settings->invoice_prefix ?: 'SHSN';
        $count = Invoice::where('invoice_number', 'like', "{$prefix}-".date('Y').'-%')->count() + 1;
        $validated['invoice_number'] = $prefix.'-'.date('Y').'-'.str_pad($count, 3, '0', STR_PAD_LEFT);

        $invoice = Invoice::create($validated);

        // Associate any unlinked payments for this shoot with this invoice
        $shoot->payments()->whereNull('invoice_id')->update(['invoice_id' => $invoice->id]);

        $telecallerNote = $telecaller ? " using telecaller {$telecaller->name}'s invoice profile" : '';
        ActivityLog::log('invoice_generated', "Tax invoice {$invoice->invoice_number} generated for shoot {$shoot->shoot_number}{$telecallerNote}.", $shoot);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Tax Invoice {$invoice->invoice_number} generated successfully based on ".($telecaller ? "{$telecaller->name}'s" : 'studio').' invoice settings!');
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['shoot.package', 'shoot.payments', 'customer', 'payments', 'telecaller', 'lead']);
        $settings = $invoice->getSettings();

        return view('invoices.show', compact('invoice', 'settings'));
    }

    public function clientGallery(string $shootNumber): View
    {
        $shoot = Shoot::with(['delivery', 'customer', 'package'])
            ->where('shoot_number', $shootNumber)
            ->firstOrFail();

        return view('portal.gallery', compact('shoot'));
    }
}
