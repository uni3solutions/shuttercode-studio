<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'shoot_id',
        'customer_id',
        'invoice_id',
        'amount',
        'payment_method',
        'payment_type',
        'transaction_reference',
        'status',
        'paid_at',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function shoot(): BelongsTo
    {
        return $this->belongsTo(Shoot::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getAssociatedInvoiceAttribute(): ?Invoice
    {
        return $this->invoice ?? $this->shoot?->invoices()->latest()->first();
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            if (! $payment->invoice_id && $payment->shoot_id) {
                $shoot = $payment->shoot ?? Shoot::find($payment->shoot_id);
                if ($shoot) {
                    $invoice = $shoot->ensureInvoice();
                    $payment->invoice_id = $invoice->id;
                }
            }
        });

        static::saved(function (Payment $payment) {
            $payment->shoot?->recalculateFinancials();
        });

        static::deleted(function (Payment $payment) {
            $payment->shoot?->recalculateFinancials();
        });
    }
}
