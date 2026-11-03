<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'shoot_id',
        'customer_id',
        'lead_id',
        'telecaller_id',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'status',
        'issue_date',
        'due_date',
        'terms',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
            'issue_date' => 'date',
            'due_date' => 'date',
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

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function telecaller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'telecaller_id');
    }

    public function getSettings(): StudioSetting
    {
        if ($this->telecaller_id && $this->telecaller) {
            return StudioSetting::forUser($this->telecaller);
        }

        if ($this->lead_id && $this->lead) {
            $tc = $this->lead->getTelecaller();
            if ($tc) {
                return StudioSetting::forUser($tc);
            }
        }

        if ($this->shoot) {
            $tc = $this->shoot->getTelecaller();
            if ($tc) {
                return StudioSetting::forUser($tc);
            }
        }

        return StudioSetting::current();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getAmountInWordsAttribute(): string
    {
        return self::formatAmountInWords((float) $this->total_amount);
    }

    public static function formatAmountInWords(float|int $rawAmount): string
    {
        $amount = (int) round((float) $rawAmount);
        if ($amount === 0) {
            return 'Zero Rupees Only';
        }

        $words = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen',
            20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty',
            60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety',
        ];

        $convertTwoDigits = function (int $num) use ($words): string {
            if ($num < 20) {
                return $words[$num] ?? '';
            }
            $tens = ((int) ($num / 10)) * 10;
            $units = $num % 10;

            return trim(($words[$tens] ?? '').' '.($words[$units] ?? ''));
        };

        $convertThreeDigits = function (int $num) use ($convertTwoDigits, $words): string {
            $hundreds = (int) ($num / 100);
            $remainder = $num % 100;
            $res = '';
            if ($hundreds > 0) {
                $res .= ($words[$hundreds] ?? '').' Hundred';
            }
            if ($remainder > 0) {
                $res .= ($res ? ' and ' : '').$convertTwoDigits($remainder);
            }

            return $res;
        };

        $crores = (int) ($amount / 10000000);
        $amount %= 10000000;
        $lakhs = (int) ($amount / 100000);
        $amount %= 100000;
        $thousands = (int) ($amount / 1000);
        $amount %= 1000;
        $remaining = $amount;

        $parts = [];
        if ($crores > 0) {
            $parts[] = $convertThreeDigits($crores).' Crore';
        }
        if ($lakhs > 0) {
            $parts[] = $convertThreeDigits($lakhs).' Lakh';
        }
        if ($thousands > 0) {
            $parts[] = $convertThreeDigits($thousands).' Thousand';
        }
        if ($remaining > 0) {
            $parts[] = $convertThreeDigits($remaining);
        }

        return 'Rupees '.implode(' ', array_filter($parts)).' Only';
    }
}
