<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketOption extends Model
{
    public $incrementing = false;

    protected $guarded = [];

    protected $hidden = ['cost'];

    protected function casts(): array
    {
        return [
            'available' => 'boolean',
            'delivery_methods' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function ticketCategory(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category');
    }
}
