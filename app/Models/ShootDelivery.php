<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShootDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'shoot_id',
        'raw_photos_count',
        'raw_drive_link',
        'edited_photos_count',
        'client_gallery_link',
        'gallery_pin',
        'album_preview_link',
        'video_links',
        'delivery_status',
        'delivered_at',
        'submitted_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'raw_photos_count' => 'integer',
            'edited_photos_count' => 'integer',
            'delivered_at' => 'datetime',
        ];
    }

    public function shoot(): BelongsTo
    {
        return $this->belongsTo(Shoot::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
