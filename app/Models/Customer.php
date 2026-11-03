<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'alternate_phone',
        'address',
        'city',
        'notes',
        'lead_id',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function shoots(): HasMany
    {
        return $this->hasMany(Shoot::class)->orderBy('shoot_date', 'desc');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('paid_at', 'desc');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->orderBy('issue_date', 'desc');
    }

    public function getTotalSpentAttribute(): float
    {
        return (float) $this->payments()->where('status', 'verified')->sum('amount');
    }
}
