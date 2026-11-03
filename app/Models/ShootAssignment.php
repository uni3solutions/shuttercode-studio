<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShootAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shoot_id',
        'user_id',
        'role',
        'payout_agreed',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'payout_agreed' => 'decimal:2',
        ];
    }

    public function shoot(): BelongsTo
    {
        return $this->belongsTo(Shoot::class);
    }

    public function photographer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
