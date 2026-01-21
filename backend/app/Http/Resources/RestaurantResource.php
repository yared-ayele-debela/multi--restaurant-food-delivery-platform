<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Restaurant */
class RestaurantResource extends JsonResource
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
            'phone' => $this->phone,
            'address_line' => $this->address_line,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'delivery_fee' => $this->delivery_fee,
            'minimum_order_amount' => $this->minimum_order_amount,
            'is_active' => $this->is_active,
            'status' => $this->status,
            'images' => RestaurantImageResource::collection($this->whenLoaded('images')),
            'hours' => RestaurantHourResource::collection($this->whenLoaded('hours')),
            'branches' => RestaurantBranchResource::collection($this->whenLoaded('branches')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
        ];
    }
}
