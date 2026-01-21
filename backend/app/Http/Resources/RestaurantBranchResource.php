<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\RestaurantBranch */
class RestaurantBranchResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'latitude' => (string) $this->latitude,
            'longitude' => (string) $this->longitude,
            'phone' => $this->phone,
            'delivery_radius_km' => (string) $this->delivery_radius,
            'preparation_time_minutes' => $this->preparation_time,
            'is_active' => $this->is_active,
        ];
    }
}
