<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudioSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'studio_name',
        'studio_tagline',
        'logo_path',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'pincode',
        'gstin',
        'default_tax_rate',
        'hsn_sac_code',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_ifsc',
        'bank_branch',
        'upi_id',
        'invoice_prefix',
        'invoice_terms',
        'invoice_notes',
        'authorized_signatory_name',
        'authorized_signatory_title',
    ];

    protected function casts(): array
    {
        return [
            'default_tax_rate' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function createDefault(): self
    {
        return static::create([
            'user_id' => null,
            'studio_name' => 'ShutterCode Studio',
            'studio_tagline' => 'Fine Art Photography & Cinematography',
            'phone' => '+91 98450 00001',
            'email' => 'billing@shuttercode.com',
            'address' => '84, 100 Feet Road, Indiranagar',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'pincode' => '560038',
            'gstin' => '29AAAAA0000A1Z5',
            'default_tax_rate' => 18.00,
            'hsn_sac_code' => '9983',
            'bank_name' => 'HDFC Bank Ltd',
            'bank_account_name' => 'ShutterCode Studio Private Limited',
            'bank_account_number' => '50200012345678',
            'bank_ifsc' => 'HDFC0001234',
            'bank_branch' => 'Indiranagar Branch, Bengaluru',
            'upi_id' => 'shuttercode@hdfcbank',
            'invoice_prefix' => 'SHSN',
            'invoice_terms' => "1. All payments must reference the invoice number.\n2. 50% advance required for date reservation; balance due on or before shoot day.\n3. RAW previews delivered within 7 business days of payment clearance.\n4. Cheques/Electronic transfers subject to realization.",
            'invoice_notes' => 'Thank you for trusting ShutterCode Studio with your moments!',
            'authorized_signatory_name' => 'Rahul Rao',
            'authorized_signatory_title' => 'Studio Director & Lead Artist',
        ]);
    }

    public static function current(?User $user = null): self
    {
        if ($user && $user->isTelecaller()) {
            return static::forUser($user);
        }

        $master = static::whereNull('user_id')->first();
        if ($master) {
            return $master;
        }

        return static::createDefault();
    }

    public static function forUser(User|int|null $user): self
    {
        if (! $user) {
            return static::current();
        }

        $userId = $user instanceof User ? $user->id : $user;
        $userModel = $user instanceof User ? $user : User::find($userId);

        $existing = static::where('user_id', $userId)->first();
        if ($existing) {
            return $existing;
        }

        // Initialize from master studio defaults
        $master = static::whereNull('user_id')->first() ?? static::createDefault();

        // Calculate unique prefix from telecaller name e.g. NEHA, KIRAN
        $rawName = $userModel?->name ? preg_replace('/[^A-Za-z]/', '', $userModel->name) : 'TCAL';
        $prefix = strtoupper(substr($rawName, 0, 4));
        if (strlen($prefix) < 3) {
            $prefix = 'TCAL';
        }

        return static::create([
            'user_id' => $userId,
            'studio_name' => $master->studio_name,
            'studio_tagline' => $master->studio_tagline,
            'logo_path' => $master->logo_path,
            'phone' => $userModel?->phone ?: $master->phone,
            'email' => $userModel?->email ?: $master->email,
            'address' => $master->address,
            'city' => $master->city,
            'state' => $master->state,
            'pincode' => $master->pincode,
            'gstin' => $master->gstin,
            'default_tax_rate' => $master->default_tax_rate,
            'hsn_sac_code' => $master->hsn_sac_code,
            'bank_name' => $master->bank_name,
            'bank_account_name' => $master->bank_account_name,
            'bank_account_number' => $master->bank_account_number,
            'bank_ifsc' => $master->bank_ifsc,
            'bank_branch' => $master->bank_branch,
            'upi_id' => $master->upi_id,
            'invoice_prefix' => $prefix,
            'invoice_terms' => $master->invoice_terms,
            'invoice_notes' => $master->invoice_notes,
            'authorized_signatory_name' => $userModel?->name ?: $master->authorized_signatory_name,
            'authorized_signatory_title' => $userModel?->specialization ?: 'Client Relationship Lead',
        ]);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo_path) {
            if (str_starts_with($this->logo_path, 'http') || str_starts_with($this->logo_path, '/')) {
                return $this->logo_path;
            }

            return asset('storage/'.$this->logo_path);
        }

        if ($this->studio_name === 'ShutterCode Studio' || empty($this->studio_name)) {
            return asset('images/logo.svg');
        }

        return null;
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([$this->address, $this->city, $this->state]);
        $str = implode(', ', $parts);
        if ($this->pincode) {
            $str .= ' — '.$this->pincode;
        }

        return $str;
    }
}
