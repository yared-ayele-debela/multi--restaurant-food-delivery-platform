<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Delivery */
class DeliveryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'driver_id' => $this->driver_id,
            'pickup_latitude' => (string) $this->pickup_latitude,
            'pickup_longitude' => (string) $this->pickup_longitude,
            'dropoff_latitude' => (string) $this->dropoff_latitude,
            'dropoff_longitude' => (string) $this->dropoff_longitude,
            'delivery_fee' => (string) $this->delivery_fee,
            'driver_earning' => (string) $this->driver_earning,
            'assigned_at' => $this->assigned_at?->toIso8601String(),
            'driver' => $this->when(
                $this->relationLoaded('driver') && $this->driver,
                function () {
                    return [
                        'id' => $this->driver->id,
                        'user_id' => $this->driver->user_id,
                        'is_approved' => $this->driver->is_approved,
                        'user' => $this->when(
                            $this->driver->relationLoaded('user'),
                            fn () => [
                                'id' => $this->driver->user->id,
                                'name' => $this->driver->user->name,
                            ]
                        ),
                    ];
                }
            ),
        ];
    }
}
