<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'seller_ntn_cnic',
        'seller_business_name',
        'seller_province',
        'seller_address',
        'fbr_sandbox_token',
        'business_activity',
        'sector',
    ];

    protected $hidden = [
        'fbr_sandbox_token',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function fbrSubmissions(): HasMany
    {
        return $this->hasMany(FbrSubmission::class);
    }

    public function setFbrSandboxTokenAttribute(?string $value): void
    {
        $this->attributes['fbr_sandbox_token'] = filled($value)
            ? Crypt::encryptString($value)
            : null;
    }

    public function getDecryptedFbrSandboxToken(): ?string
    {
        if (blank($this->attributes['fbr_sandbox_token'] ?? null)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->attributes['fbr_sandbox_token']);
        } catch (\Throwable) {
            return null;
        }
    }

    public function hasSandboxToken(): bool
    {
        return filled($this->getDecryptedFbrSandboxToken());
    }

    public function sellerProfileComplete(): bool
    {
        return filled($this->seller_ntn_cnic)
            && filled($this->seller_business_name)
            && filled($this->seller_province)
            && filled($this->seller_address);
    }
}
