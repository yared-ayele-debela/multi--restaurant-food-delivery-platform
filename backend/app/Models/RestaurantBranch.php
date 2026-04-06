<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestaurantBranch extends Model
{
    /** @use HasFactory<\Database\Factories\RestaurantBranchFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'name',
        'address',
        'city',
        'state',
        'postal_code',
        'latitude',
        'longitude',
        'phone',
        'image_path',
        'delivery_radius',
        'preparation_time',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'delivery_radius' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
