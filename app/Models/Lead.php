<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_number',
        'customer_name',
        'phone',
        'email',
        'event_type',
        'event_date',
        'venue',
        'estimated_budget',
        'source',
        'assigned_to',
        'created_by',
        'status',
        'notes',
    ];

    public const STATUS_NEW = 'new';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_IN_DISCUSSION = 'in_discussion';

    public const STATUS_INTERESTED = 'interested';

    public const STATUS_FOLLOW_UP_LATER = 'follow_up_later';

    public const STATUS_NOT_RESPONDING = 'not_responding';

    public const STATUS_NOT_INTERESTED = 'not_interested';

    public const STATUS_CONVERTED = 'converted';

    /**
     * Pipeline status definitions with user-friendly labels.
     *
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_NEW => 'New Enquiry',
            self::STATUS_CONTACTED => 'Contacted',
            self::STATUS_IN_DISCUSSION => 'In Discussion',
            self::STATUS_INTERESTED => 'Interested',
            self::STATUS_FOLLOW_UP_LATER => 'Follow Up Later',
            self::STATUS_NOT_RESPONDING => 'Not Responding',
            self::STATUS_NOT_INTERESTED => 'Not Interested',
            self::STATUS_CONVERTED => 'Converted to Booking',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statuses()[$this->status] ?? ucwords(str_replace('_', ' ', $this->status));
    }

    public function getStatusVariantAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => 'info',
            self::STATUS_CONTACTED => 'sky',
            self::STATUS_IN_DISCUSSION => 'amber',
            self::STATUS_INTERESTED => 'purple',
            self::STATUS_FOLLOW_UP_LATER => 'indigo',
            self::STATUS_NOT_RESPONDING => 'neutral',
            self::STATUS_NOT_INTERESTED, 'lost' => 'danger',
            self::STATUS_CONVERTED => 'success',
            default => 'neutral',
        };
    }

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'estimated_budget' => 'decimal:2',
        ];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->orderBy('created_at', 'desc');
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function shoots(): HasMany
    {
        return $this->hasMany(Shoot::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function getTelecaller(): ?User
    {
        if ($this->assignedUser?->isTelecaller()) {
            return $this->assignedUser;
        }

        if ($this->creator?->isTelecaller()) {
            return $this->creator;
        }

        return $this->assignedUser ?? $this->creator;
    }

    public function getWhatsAppUrlAttribute(): string
    {
        // Strip non-numeric characters
        $cleanPhone = preg_replace('/[^0-9]/', '', $this->phone);
        // Default to India country code 91 if 10 digits
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91'.$cleanPhone;
        }

        $message = urlencode("Hello {$this->customer_name}, greetings from ShutterCode Studio! Regarding your enquiry for {$this->event_type}, we'd love to share our packages.");

        return "https://wa.me/{$cleanPhone}?text={$message}";
    }
}
