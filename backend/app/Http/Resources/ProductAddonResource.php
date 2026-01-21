<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ProductAddon */
class ProductAddonResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => (string) $this->price,
            'is_active' => $this->is_active,
            'max_quantity' => $this->max_quantity,
            'group_name' => $this->group_name,
            'sort_order' => $this->sort_order,
        ];
    }
}
