<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'margin',
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

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AccessCodeLog::class);
    }

    public function applyMargin(float $cost): float
    {
        return ceil($cost * (1 + $this->margin / 100) / 5) * 5;
    }

    public function activate(): void
    {
        if ($this->activated_at) {
            return;
        }

        $this->update([
            'activated_at' => now(),
            'expires_at' => $this->expires_after ? now()->addMinutes($this->expires_after) : null,
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
