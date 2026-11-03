<?php

use App\Models\Shoot;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:sync-invoices', function () {
    $shoots = Shoot::with(['payments', 'invoices'])->get();
    foreach ($shoots as $shoot) {
        if ($shoot->payments->isNotEmpty()) {
            $invoice = $shoot->ensureInvoice();
            foreach ($shoot->payments as $payment) {
                if (! $payment->invoice_id) {
                    $payment->invoice_id = $invoice->id;
                    $payment->save();
                }
            }
        }
        $shoot->recalculateFinancials();
    }
    $this->info('Invoices and payments synced successfully.');
})->purpose('Ensure all shoots with payments have invoices and payments are linked');
