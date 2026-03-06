<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Driver;
use App\Models\Order;
use App\Models\UserAddress;
use Illuminate\Validation\ValidationException;

class OrderDeliveryService
{
    /**
     * Create or refresh the delivery row for an order in "ready" state (pickup/dropoff coordinates).
     */
    public function ensureForOrder(Order $order): Delivery
    {
        $order->loadMissing(['restaurant', 'branch', 'address']);

        [$pickupLat, $pickupLng] = $this->resolvePickup($order);
        [$dropLat, $dropLng] = $this->resolveDropoff($order);

        return Delivery::query()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'pickup_latitude' => $pickupLat,
                'pickup_longitude' => $pickupLng,
                'dropoff_latitude' => $dropLat,
                'dropoff_longitude' => $dropLng,
                'delivery_fee' => $order->delivery_fee,
                'driver_earning' => $order->driver_earnings,
                'status' => 'pending',
            ]
        );
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function resolvePickup(Order $order): array
    {
        if ($order->branch_id && $order->branch) {
            $b = $order->branch;
            if ($b->latitude !== null && $b->longitude !== null) {
                return [(float) $b->latitude, (float) $b->longitude];
            }
        }

        $r = $order->restaurant;
        if ($r && $r->latitude !== null && $r->longitude !== null) {
            return [(float) $r->latitude, (float) $r->longitude];
        }

        throw ValidationException::withMessages([
            'order' => ['Pickup location is missing coordinates for this restaurant or branch.'],
        ]);
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function resolveDropoff(Order $order): array
    {
        $snap = $order->delivery_address;
        if (is_array($snap)) {
            $lat = $snap['latitude'] ?? null;
            $lng = $snap['longitude'] ?? null;
            if ($lat !== null && $lat !== '' && $lng !== null && $lng !== '') {
                return [(float) $lat, (float) $lng];
            }
        }

        if ($order->address_id) {
            $addr = $order->address ?? UserAddress::query()->whereKey($order->address_id)->first();
            if ($addr && $addr->latitude !== null && $addr->longitude !== null) {
                return [(float) $addr->latitude, (float) $addr->longitude];
            }
        }

        throw ValidationException::withMessages([
            'order' => ['Drop-off coordinates are missing; use a saved address with location or ensure checkout includes latitude and longitude.'],
        ]);
    }

    public function assignDriver(Order $order, int $driverId): Delivery
    {
        $order->loadMissing('delivery');

        $delivery = $order->delivery;
        if (! $delivery) {
            throw ValidationException::withMessages([
                'order' => ['No delivery record exists for this order.'],
            ]);
        }

        $driver = Driver::query()->whereKey($driverId)->firstOrFail();

        if (! $driver->is_approved) {
            throw ValidationException::withMessages([
                'driver_id' => ['This driver is not approved for deliveries.'],
            ]);
        }

        $delivery->update([
            'driver_id' => $driver->id,
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        return $delivery->fresh();
    }
}
