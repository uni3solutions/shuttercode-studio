<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'price',
        'advance_amount',
        'duration_hours',
        'photographers_count',
        'videographers_count',
        'drone_included',
        'album_count',
        'deliverables',
        'description',
        'created_by',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'advance_amount' => 'decimal:2',
            'drone_included' => 'boolean',
            'is_active' => 'boolean',
            'duration_hours' => 'integer',
            'photographers_count' => 'integer',
            'videographers_count' => 'integer',
            'album_count' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function shoots(): HasMany
    {
        return $this->hasMany(Shoot::class);
    }
}
