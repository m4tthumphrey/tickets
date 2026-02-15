<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessCode extends Model
{
    protected $fillable = [
        'code',
        'label',
        'default_filters',
        'expires_after',
        'activated_at',
        'expires_at',
        'is_revoked',
    ];

    protected function casts(): array
    {
        return [
            'default_filters' => 'array',
            'activated_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_revoked' => 'boolean',
        ];
    }

    public function activate(): void
    {
        if ($this->activated_at) {
            return;
        }

        $this->update([
            'activated_at' => now(),
            'expires_at' => now()->addMinutes($this->expires_after),
        ]);
    }

    public function isValid(): bool
    {
        return $this->invalidReason() === null;
    }

    public function invalidReason(): ?string
    {
        if ($this->is_revoked) {
            return 'This code has been revoked.';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'This code has expired.';
        }

        return null;
    }
}
