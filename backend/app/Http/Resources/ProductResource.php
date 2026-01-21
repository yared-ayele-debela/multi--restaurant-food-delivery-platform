<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Product */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->image,
            'base_price' => (string) $this->base_price,
            'discount_price' => $this->discount_price !== null ? (string) $this->discount_price : null,
            'preparation_time' => $this->preparation_time,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'sort_order' => $this->sort_order,
            'dietary_info' => $this->dietary_info,
            'allergens' => $this->allergens,
            'calories' => $this->calories,
            'category_id' => $this->category_id,
            'sizes' => ProductSizeResource::collection($this->whenLoaded('sizes')),
            'addons' => ProductAddonResource::collection($this->whenLoaded('addons')),
            'stock' => ProductStockResource::collection($this->whenLoaded('stock')),
        ];
    }
}
