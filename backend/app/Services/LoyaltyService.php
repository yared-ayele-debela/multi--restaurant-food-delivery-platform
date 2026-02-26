<?php

namespace App\Services;

use App\Models\LoyaltyLevel;
use App\Models\LoyaltyPoints;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class LoyaltyService
{
    /**
     * How many loyalty points redeem for one currency unit (e.g. 100 points = $1 off).
     */
    private function pointsPerCurrencyUnit(): int
    {
        return max(1, (int) config('food-delivery.loyalty.points_per_currency_unit', 100));
    }

    /**
     * Points earned per $1 of eligible subtotal on order completion.
     */
    private function pointsPerDollarEarned(): float
    {
        return (float) config('food-delivery.loyalty.points_per_dollar', 10);
    }

    /**
     * @return array{discount: float, points: int}
     */
    public function calculateRedemption(User $user, int $redeemPoints, float $subtotalAfterCoupon): array
    {
        if ($redeemPoints <= 0) {
            return ['discount' => 0.0, 'points' => 0];
        }

        $record = $this->getOrCreatePoints($user);
        if ($record->available_points < $redeemPoints) {
            throw ValidationException::withMessages([
                'redeem_loyalty_points' => ['Not enough loyalty points available.'],
            ]);
        }

        $unit = $this->pointsPerCurrencyUnit();
        $maxDiscount = round($redeemPoints / $unit, 2);
        $discount = round(min($maxDiscount, $subtotalAfterCoupon), 2);
        $points = (int) min($redeemPoints, (int) ceil($discount * $unit));

        return ['discount' => $discount, 'points' => $points];
    }

    /**
     * Deduct redeemed points and write loyalty_transactions (call inside checkout transaction).
     */
    public function commitRedemptionForOrder(User $user, Order $order, int $points, float $discount): void
    {
        if ($points <= 0) {
            return;
        }

        $record = LoyaltyPoints::query()->where('user_id', $user->id)->lockForUpdate()->firstOrFail();
        if ($record->available_points < $points) {
            throw ValidationException::withMessages([
                'redeem_loyalty_points' => ['Not enough loyalty points available.'],
            ]);
        }

        $before = (int) $record->available_points;
        $after = $before - $points;

        $record->update([
            'available_points' => $after,
            'redeemed_points' => $record->redeemed_points + $points,
        ]);

        LoyaltyTransaction::query()->create([
            'user_id' => $user->id,
            'type' => 'redeemed',
            'points' => -$points,
            'balance_before' => $before,
            'balance_after' => $after,
            'source_type' => Order::class,
            'source_id' => $order->id,
            'description' => 'Redeemed on order '.$order->order_number,
            'expires_at' => null,
            'created_at' => now(),
        ]);
    }

    public function accrueForCompletedOrder(Order $order): void
    {
        $order->loadMissing('user');
        $user = $order->user;
        if (! $user) {
            return;
        }

        $base = round((float) $order->restaurant_earnings + (float) $order->commission_amount, 2);
        if ($base <= 0) {
            return;
        }

        $referenceType = Order::class;
        if (LoyaltyTransaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'earned')
            ->where('source_type', $referenceType)
            ->where('source_id', $order->id)
            ->exists()) {
            return;
        }

        $record = $this->getOrCreatePoints($user);
        $record = LoyaltyPoints::query()->whereKey($record->id)->with('level')->lockForUpdate()->firstOrFail();

        $multiplier = (float) ($record->level?->multiplier ?? 1);
        $points = (int) floor($base * $this->pointsPerDollarEarned() * $multiplier);
        if ($points <= 0) {
            return;
        }

        $before = (int) $record->available_points;
        $after = $before + $points;

        $record->update([
            'total_points' => $record->total_points + $points,
            'available_points' => $after,
        ]);

        $this->syncLevel($record->fresh());

        LoyaltyTransaction::query()->create([
            'user_id' => $user->id,
            'type' => 'earned',
            'points' => $points,
            'balance_before' => $before,
            'balance_after' => $after,
            'source_type' => $referenceType,
            'source_id' => $order->id,
            'description' => 'Points for order '.$order->order_number,
            'expires_at' => null,
            'created_at' => now(),
        ]);

        $order->update(['loyalty_points_earned' => $points]);
    }

    public function getOrCreatePoints(User $user): LoyaltyPoints
    {
        return LoyaltyPoints::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'total_points' => 0,
                'available_points' => 0,
                'redeemed_points' => 0,
                'level_id' => 1,
            ]
        );
    }

    private function syncLevel(LoyaltyPoints $record): void
    {
        $total = (int) $record->total_points;
        $level = LoyaltyLevel::query()
            ->where('min_points', '<=', $total)
            ->orderByDesc('min_points')
            ->first();

        if ($level && (int) $record->level_id !== (int) $level->id) {
            $record->update(['level_id' => $level->id]);
        }
    }
}
