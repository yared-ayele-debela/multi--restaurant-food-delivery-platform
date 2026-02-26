<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyPoints extends Model
{
    protected $table = 'loyalty_points';

    protected $fillable = [
        'user_id',
        'total_points',
        'available_points',
        'redeemed_points',
        'level_id',
    ];

    protected function casts(): array
    {
        return [
            'total_points' => 'integer',
            'available_points' => 'integer',
            'redeemed_points' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(LoyaltyLevel::class, 'level_id');
    }
}
