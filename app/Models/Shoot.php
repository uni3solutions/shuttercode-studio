<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Shoot extends Model
{
    use HasFactory;

    protected $fillable = [
        'shoot_number',
        'title',
        'customer_id',
        'package_id',
        'lead_id',
        'event_type',
        'shoot_date',
        'start_time',
        'end_time',
        'venue',
        'location_url',
        'requirements',
        'notes',
        'status',
        'total_amount',
        'discount_amount',
        'advance_amount',
        'paid_amount',
        'balance_amount',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'shoot_date' => 'date',
            'total_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'advance_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTelecaller(): ?User
    {
        // 1. Direct lead assigned/creator
        if ($this->lead_id && $this->lead) {
            $tc = $this->lead->getTelecaller();
            if ($tc) {
                return $tc;
            }
        }

        // 2. Customer linked lead
        if ($this->customer?->lead_id && $this->customer->lead) {
            $tc = $this->customer->lead->getTelecaller();
            if ($tc) {
                return $tc;
            }
        }

        // 3. Match lead by customer phone
        if ($this->customer?->phone) {
            $matchedLead = Lead::where('phone', $this->customer->phone)->latest()->first();
            if ($matchedLead) {
                $tc = $matchedLead->getTelecaller();
                if ($tc) {
                    return $tc;
                }
            }
        }

        // 4. Shoot creator if telecaller
        if ($this->creator?->isTelecaller()) {
            return $this->creator;
        }

        return null;
    }

    /**
     * Scope query to shoots owned or converted by a specific telecaller user.
     */
    public function scopeForTelecaller(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $q) use ($userId) {
            $q->where('shoots.created_by', $userId)
                ->orWhereHas('lead', function (Builder $lq) use ($userId) {
                    $lq->where('created_by', $userId)
                        ->orWhere('assigned_to', $userId);
                })
                ->orWhereHas('customer.lead', function (Builder $clq) use ($userId) {
                    $clq->where('created_by', $userId)
                        ->orWhere('assigned_to', $userId);
                })
                ->orWhereHas('customer', function (Builder $cq) use ($userId) {
                    $cq->whereIn('phone', function ($sub) use ($userId) {
                        $sub->select('phone')
                            ->from('leads')
                            ->whereNotNull('phone')
                            ->where('phone', '!=', '')
                            ->where(function ($leadSub) use ($userId) {
                                $leadSub->where('created_by', $userId)
                                    ->orWhere('assigned_to', $userId);
                            });
                    });
                });
        });
    }

    /**
     * Check if shoot is owned or converted by a given user.
     */
    public function isOwnedOrConvertedBy(User $user): bool
    {
        if ($this->created_by === $user->id) {
            return true;
        }

        $tc = $this->getTelecaller();
        if ($tc && $tc->id === $user->id) {
            return true;
        }

        if ($this->lead && ($this->lead->created_by === $user->id || $this->lead->assigned_to === $user->id)) {
            return true;
        }

        if ($this->customer?->lead && ($this->customer->lead->created_by === $user->id || $this->customer->lead->assigned_to === $user->id)) {
            return true;
        }

        return false;
    }

    /**
     * Resolve telecaller attribution details (label and user).
     *
     * @return array{user: ?User, label: ?string}
     */
    public function getTelecallerAttribution(): array
    {
        $tc = $this->getTelecaller();
        if (! $tc) {
            return [
                'user' => null,
                'label' => null,
            ];
        }

        $label = ($this->lead_id || $this->customer?->lead_id)
            ? 'Converted by'
            : ($this->created_by === $tc->id ? 'Created by' : 'Telecaller');

        return [
            'user' => $tc,
            'label' => $label,
        ];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ShootAssignment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('paid_at', 'desc');
    }

    public function photographerPayments(): HasMany
    {
        return $this->hasMany(PhotographerPayment::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(ShootDelivery::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Ensure a Tax Invoice exists for this shoot, auto-generating if needed.
     */
    public function ensureInvoice(): Invoice
    {
        $invoice = $this->invoices()->latest()->first();
        if ($invoice) {
            return $invoice;
        }

        $settings = StudioSetting::current();
        $invCount = Invoice::whereYear('created_at', date('Y'))->count() + 1;
        $prefix = $settings->invoice_prefix ?: 'SHSN';
        $invNumber = $prefix.'-'.date('Y').'-'.str_pad($invCount, 3, '0', STR_PAD_LEFT);
        $subtotal = $this->total_amount;
        $discount = $this->discount_amount ?? 0;
        $taxRate = $settings->default_tax_rate ?? 18.00;
        $taxAmount = round(($subtotal - $discount) * ($taxRate / 100), 2);
        $totalAmount = ($subtotal - $discount) + $taxAmount;

        $verifiedPayments = $this->payments()->where('status', 'verified')->sum('amount');
        $paid = min($totalAmount, $verifiedPayments);
        $balance = max(0, $totalAmount - $paid);
        $status = $balance <= 0 ? 'paid' : ($paid > 0 ? 'partially_paid' : 'sent');

        $invoice = Invoice::create([
            'invoice_number' => $invNumber,
            'shoot_id' => $this->id,
            'customer_id' => $this->customer_id,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discount,
            'total_amount' => $totalAmount,
            'paid_amount' => $paid,
            'balance_amount' => $balance,
            'status' => $status,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'terms' => $settings->invoice_terms ?: 'Payment due within 15 days of issue date. Standard terms apply.',
        ]);

        // Associate any unlinked payments for this shoot
        $this->payments()->whereNull('invoice_id')->update(['invoice_id' => $invoice->id]);

        return $invoice;
    }

    /**
     * Recalculate paid and balance amounts from verified transactions.
     */
    public function recalculateFinancials(): void
    {
        $verifiedPayments = $this->payments()->where('status', 'verified')->sum('amount');
        $this->paid_amount = $verifiedPayments;
        $netTotal = max(0, $this->total_amount - $this->discount_amount);
        $this->balance_amount = max(0, $netTotal - $verifiedPayments);
        $this->save();

        // Sync all shoot invoices
        foreach ($this->invoices as $invoice) {
            $invoice->paid_amount = min($invoice->total_amount, $verifiedPayments);
            $invoice->balance_amount = max(0, $invoice->total_amount - $invoice->paid_amount);
            $invoice->status = $invoice->balance_amount <= 0 ? 'paid' : ($invoice->paid_amount > 0 ? 'partially_paid' : 'sent');
            $invoice->save();
        }
    }
}
