<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'category_id',
        'name',
        'slug',
        'description',
        'image',
        'base_price',
        'discount_price',
        'preparation_time',
        'is_active',
        'is_featured',
        'sort_order',
        'dietary_info',
        'allergens',
        'calories',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'dietary_info' => 'array',
            'allergens' => 'array',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(ProductSize::class);
    }

    public function addons(): HasMany
    {
        return $this->hasMany(ProductAddon::class);
    }

    public function stock(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    public function defaultSize(): ?ProductSize
    {
        return $this->sizes()->where('is_default', true)->first()
            ?? $this->sizes()->orderBy('sort_order')->first();
    }
}
