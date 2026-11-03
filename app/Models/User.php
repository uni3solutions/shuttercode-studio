<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'specialization',
        'avatar',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function isTelecaller(): bool
    {
        return $this->role === 'telecaller';
    }

    public function isPhotographer(): bool
    {
        return $this->role === 'photographer';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function canManageShoots(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'telecaller']);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ShootAssignment::class, 'user_id');
    }

    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function createdLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'created_by');
    }

    public function createdPackages(): HasMany
    {
        return $this->hasMany(Package::class, 'created_by');
    }

    public function createdShoots(): HasMany
    {
        return $this->hasMany(Shoot::class, 'created_by');
    }

    public function photographerPayments(): HasMany
    {
        return $this->hasMany(PhotographerPayment::class, 'user_id');
    }

    public function studioSetting(): HasOne
    {
        return $this->hasOne(StudioSetting::class, 'user_id');
    }

    public function getInvoiceSetting(): StudioSetting
    {
        return StudioSetting::forUser($this);
    }
}
