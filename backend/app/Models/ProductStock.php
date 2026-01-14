<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStock extends Model
{
    protected $table = 'product_stock';

    protected $fillable = [
        'product_id',
        'branch_id',
        'quantity',
        'low_stock_threshold',
        'track_stock',
    ];

    protected function casts(): array
    {
        return [
            'track_stock' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(RestaurantBranch::class);
    }
}
