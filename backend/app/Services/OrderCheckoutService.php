<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\ProductSize;
use App\Models\ProductStock;
use App\Models\Restaurant;
use App\Models\RestaurantBranch;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderCheckoutService
{
    public function __construct(
        private CouponValidationService $couponValidationService,
        private LoyaltyService $loyaltyService,
    ) {}

    public function placeOrder(User $user, array $input): Order
    {
        $restaurant = Restaurant::query()->findOrFail($input['restaurant_id']);

        if (! $restaurant->isPublicInCatalog()) {
            throw ValidationException::withMessages([
                'restaurant_id' => ['This restaurant is not accepting orders.'],
            ]);
        }

        $branch = $this->resolveBranch($restaurant, $input['branch_id'] ?? null);
        $addressSnapshot = $this->buildAddressSnapshot($user, $input);

        $lines = $this->buildLines($restaurant, $branch?->id, $input['items']);

        $itemsSubtotal = round(collect($lines)->sum('line_subtotal'), 2);
        $baseDeliveryFee = (float) $restaurant->delivery_fee;

        $couponPayload = $this->couponValidationService->validateForCheckout(
            $user,
            $restaurant,
            $input['coupon_code'] ?? null,
            $itemsSubtotal,
            $baseDeliveryFee
        );

        $coupon = $couponPayload['coupon'];
        $itemCouponDiscount = $couponPayload['item_discount'];
        $deliveryWaiver = $couponPayload['delivery_discount'];
        $deliveryFeeCharged = $couponPayload['delivery_fee_charged'];

        $subAfterCoupon = round($itemsSubtotal - $itemCouponDiscount, 2);

        $loyaltyRedeem = $this->loyaltyService->calculateRedemption(
            $user,
            (int) ($input['redeem_loyalty_points'] ?? 0),
            $subAfterCoupon
        );
        $loyaltyDiscount = $loyaltyRedeem['discount'];
        $loyaltyPointsUsed = $loyaltyRedeem['points'];

        $subtotalAfterDiscount = round($subAfterCoupon - $loyaltyDiscount, 2);

        if ($subtotalAfterDiscount < (float) $restaurant->minimum_order_amount) {
            throw ValidationException::withMessages([
                'items' => [
                    'Subtotal must be at least '.$restaurant->minimum_order_amount.' for this restaurant.',
                ],
            ]);
        }

        $taxRate = (float) config('food-delivery.tax_rate');
        $taxAmount = round($subtotalAfterDiscount * ($taxRate / 100), 2);
        $total = round($subtotalAfterDiscount + $deliveryFeeCharged + $taxAmount, 2);

        $commissionRate = (float) ($restaurant->commission_rate ?? 15);
        $commissionAmount = round($subtotalAfterDiscount * ($commissionRate / 100), 2);
        $restaurantEarnings = round($subtotalAfterDiscount - $commissionAmount, 2);

        $totalDiscountAmount = round($itemCouponDiscount + $deliveryWaiver + $loyaltyDiscount, 2);

        return DB::transaction(function () use (
            $user,
            $restaurant,
            $branch,
            $input,
            $lines,
            $itemsSubtotal,
            $totalDiscountAmount,
            $deliveryFeeCharged,
            $taxRate,
            $taxAmount,
            $total,
            $commissionRate,
            $commissionAmount,
            $restaurantEarnings,
            $addressSnapshot,
            $coupon,
            $itemCouponDiscount,
            $deliveryWaiver,
            $loyaltyPointsUsed,
            $loyaltyDiscount
        ) {
            $this->applyStockDecrements($lines, $branch?->id);

            $order = Order::query()->create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $user->id,
                'restaurant_id' => $restaurant->id,
                'branch_id' => $branch?->id,
                'address_id' => $input['address_id'] ?? null,
                'coupon_id' => $coupon?->id,
                'status' => OrderStatus::Pending,
                'subtotal' => $itemsSubtotal,
                'discount_amount' => $totalDiscountAmount,
                'delivery_fee' => $deliveryFeeCharged,
                'tax_amount' => $taxAmount,
                'tax_rate' => $taxRate,
                'total' => $total,
                'commission_rate' => $commissionRate,
                'commission_amount' => $commissionAmount,
                'restaurant_earnings' => $restaurantEarnings,
                'driver_earnings' => 0,
                'payment_method' => $input['payment_method'] ?? 'cash',
                'payment_status' => 'pending',
                'stripe_payment_intent_id' => null,
                'delivery_address' => $addressSnapshot,
                'delivery_notes' => $input['delivery_notes'] ?? null,
                'placed_at' => now(),
                'loyalty_points_earned' => 0,
                'loyalty_points_redeemed' => $loyaltyPointsUsed,
            ]);

            foreach ($lines as $line) {
                $order->orderItems()->create([
                    'product_id' => $line['product']->id,
                    'product_size_id' => $line['size']->id,
                    'product_name' => $line['product']->name,
                    'product_size_name' => $line['size']->name,
                    'item_name' => $line['product']->name.' — '.$line['size']->name,
                    'unit_price' => $line['size']->price,
                    'quantity' => $line['quantity'],
                    'addons' => $line['addons_snapshot'],
                    'addons_total' => $line['addons_total'],
                    'subtotal' => $line['line_subtotal'],
                ]);
            }

            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'previous_status' => null,
                'new_status' => OrderStatus::Pending->value,
                'changed_by' => $user->id,
                'notes' => null,
                'created_at' => now(),
            ]);

            if ($coupon !== null) {
                $this->couponValidationService->recordUsage(
                    $coupon,
                    $user,
                    $order,
                    round($itemCouponDiscount + $deliveryWaiver, 2)
                );
            }

            if ($loyaltyPointsUsed > 0) {
                $this->loyaltyService->commitRedemptionForOrder($user, $order, $loyaltyPointsUsed, $loyaltyDiscount);
            }

            return $order->load(['restaurant', 'orderItems']);
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildLines(Restaurant $restaurant, ?int $branchId, array $items): array
    {
        $lines = [];

        foreach ($items as $row) {
            $product = Product::query()
                ->where('restaurant_id', $restaurant->id)
                ->whereKey($row['product_id'])
                ->with(['sizes', 'addons'])
                ->first();

            if (! $product || ! $product->is_active) {
                throw ValidationException::withMessages([
                    'items' => ['One or more products are unavailable.'],
                ]);
            }

            $size = $this->resolveSize($product, $row['product_size_id'] ?? null);
            if (! $size) {
                throw ValidationException::withMessages([
                    'items' => ["Please choose a valid size for \"{$product->name}\"."],
                ]);
            }

            $qty = (int) $row['quantity'];
            $baseLine = round((float) $size->price * $qty, 2);

            [$addonsSnapshot, $addonsTotal] = $this->resolveAddons($product, $row['addons'] ?? [], $qty);

            $lineSubtotal = round($baseLine + $addonsTotal, 2);

            $lines[] = [
                'product' => $product,
                'size' => $size,
                'quantity' => $qty,
                'addons_snapshot' => $addonsSnapshot,
                'addons_total' => $addonsTotal,
                'line_subtotal' => $lineSubtotal,
                'branch_id' => $branchId,
            ];
        }

        return $lines;
    }

    private function resolveSize(Product $product, ?int $productSizeId): ?ProductSize
    {
        if ($productSizeId) {
            return $product->sizes->firstWhere('id', $productSizeId);
        }

        return $product->sizes->where('is_default', true)->first()
            ?? $product->sizes->sortBy('sort_order')->first();
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: float}
     */
    private function resolveAddons(Product $product, array $addonRows, int $lineQuantity): array
    {
        $snapshot = [];
        $total = 0.0;

        foreach ($addonRows as $addonRow) {
            $addonId = (int) $addonRow['product_addon_id'];
            $addonQty = (int) ($addonRow['quantity'] ?? 1);

            /** @var ProductAddon|null $addon */
            $addon = $product->addons->firstWhere('id', $addonId);
            if (! $addon || ! $addon->is_active) {
                throw ValidationException::withMessages([
                    'items' => ['Invalid add-on selected for '.$product->name.'.'],
                ]);
            }

            if ($addonQty > $addon->max_quantity * $lineQuantity) {
                throw ValidationException::withMessages([
                    'items' => ['Add-on quantity too high for '.$addon->name.'.'],
                ]);
            }

            $lineAddonTotal = round((float) $addon->price * $addonQty, 2);
            $total += $lineAddonTotal;

            $snapshot[] = [
                'id' => $addon->id,
                'name' => $addon->name,
                'price' => (string) $addon->price,
                'qty' => $addonQty,
                'line_total' => (string) $lineAddonTotal,
            ];
        }

        return [$snapshot, round($total, 2)];
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    private function assertAggregatedStock(array $lines, ?int $branchId): void
    {
        $totals = [];
        foreach ($lines as $line) {
            $pid = $line['product']->id;
            $totals[$pid] = ($totals[$pid] ?? 0) + $line['quantity'];
        }

        foreach ($totals as $productId => $qty) {
            $product = Product::query()->find($productId);
            if (! $product) {
                continue;
            }

            $stocks = ProductStock::query()
                ->where('product_id', $product->id)
                ->where(function ($q) use ($branchId) {
                    $q->whereNull('branch_id');
                    if ($branchId) {
                        $q->orWhere('branch_id', $branchId);
                    }
                })
                ->get();

            if ($stocks->isEmpty()) {
                continue;
            }

            $relevant = $stocks->first(fn ($s) => $branchId && (int) $s->branch_id === $branchId)
                ?? $stocks->firstWhere('branch_id', null)
                ?? $stocks->first();

            if (! $relevant || ! $relevant->track_stock) {
                continue;
            }

            if ($relevant->quantity < $qty) {
                throw ValidationException::withMessages([
                    'items' => ['Insufficient stock for '.$product->name.'.'],
                ]);
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    private function applyStockDecrements(array $lines, ?int $branchId): void
    {
        $totals = [];
        foreach ($lines as $line) {
            $pid = $line['product']->id;
            $totals[$pid] = ($totals[$pid] ?? 0) + $line['quantity'];
        }

        foreach ($totals as $productId => $qty) {
            $stocks = ProductStock::query()
                ->where('product_id', $productId)
                ->where(function ($q) use ($branchId) {
                    $q->whereNull('branch_id');
                    if ($branchId) {
                        $q->orWhere('branch_id', $branchId);
                    }
                })
                ->lockForUpdate()
                ->get();

            $stock = $stocks->first(fn ($s) => $branchId && (int) $s->branch_id === $branchId)
                ?? $stocks->firstWhere('branch_id', null);

            if (! $stock || ! $stock->track_stock) {
                continue;
            }

            $stock->decrement('quantity', $qty);
        }
    }

    private function resolveBranch(Restaurant $restaurant, mixed $branchId): ?RestaurantBranch
    {
        if ($branchId === null || $branchId === '') {
            return null;
        }

        $branch = RestaurantBranch::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereKey((int) $branchId)
            ->first();

        if (! $branch || ! $branch->is_active) {
            throw ValidationException::withMessages([
                'branch_id' => ['Invalid or inactive branch for this restaurant.'],
            ]);
        }

        return $branch;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function buildAddressSnapshot(User $user, array $input): array
    {
        $formatted = $input['delivery_address'];

        $snapshot = [
            'formatted' => $formatted,
        ];

        if (! empty($input['address_id'])) {
            $addr = UserAddress::query()
                ->where('user_id', $user->id)
                ->whereKey((int) $input['address_id'])
                ->first();

            if (! $addr) {
                throw ValidationException::withMessages([
                    'address_id' => ['Address not found.'],
                ]);
            }

            $snapshot['address_id'] = $addr->id;
            $snapshot['label'] = $addr->label;
            $snapshot['line1'] = $addr->address_line_1;
            $snapshot['line2'] = $addr->address_line_2;
            $snapshot['city'] = $addr->city;
            $snapshot['latitude'] = (string) $addr->latitude;
            $snapshot['longitude'] = (string) $addr->longitude;
        }

        return $snapshot;
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-'.now()->format('Ymd').'-'.str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
