<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyLevel extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'min_points',
        'max_points',
        'cashback_rate',
        'free_delivery',
        'multiplier',
        'badge_color',
        'icon',
        'benefits',
    ];

    protected function casts(): array
    {
        return [
            'cashback_rate' => 'decimal:2',
            'free_delivery' => 'boolean',
            'multiplier' => 'decimal:2',
            'benefits' => 'array',
        ];
    }

    public function loyaltyPoints(): HasMany
    {
        return $this->hasMany(LoyaltyPoints::class, 'level_id');
    }
}
