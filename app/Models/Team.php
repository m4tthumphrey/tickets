<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    public $incrementing = false;

    protected $guarded = [];

    public function homeProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'home_team_id');
    }

    public function awayProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'away_team_id');
    }
}
