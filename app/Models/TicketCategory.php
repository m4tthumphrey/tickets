<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketCategory extends Model
{
    public $incrementing = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'delivery_methods' => 'array',
            'is_hospitality' => 'boolean',
            'has_food_included' => 'boolean',
            'has_drinks_included' => 'boolean',
            'has_lounge_access' => 'boolean',
            'is_family_friendly' => 'boolean',
            'has_padded_seats' => 'boolean',
            'included_extras' => 'array',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }
}
