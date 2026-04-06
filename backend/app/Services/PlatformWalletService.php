<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use App\Models\Wallet;
use App\Models\WalletTransaction;

class PlatformWalletService
{
    /**
     * Credit platform wallet with admin commission on completed order.
     * Idempotent per order.
     */
    public function creditCommissionForCompletedOrder(Order $order): void
    {
        $commission = round((float) $order->commission_amount, 2);
        if ($commission <= 0) {
            return;
        }

        $settings = Setting::getInstance();
        $referenceType = Order::class;

        $wallet = Wallet::query()->firstOrCreate(
            [
                'holder_type' => Setting::class,
                'holder_id' => $settings->id,
            ],
            [
                'balance' => 0,
                'total_earned' => 0,
                'total_withdrawn' => 0,
                'total_commission_paid' => 0,
                'currency' => config('food-delivery.currency', 'USD'),
                'is_active' => true,
            ]
        );

        $wallet = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();

        if (WalletTransaction::query()
            ->where('wallet_id', $wallet->id)
            ->where('reference_type', $referenceType)
            ->where('reference_id', $order->id)
            ->where('transaction_type', 'platform_commission')
            ->exists()) {
            return;
        }

        $before = round((float) $wallet->balance, 2);
        $after = round($before + $commission, 2);

        $wallet->update([
            'balance' => $after,
            'total_earned' => round((float) $wallet->total_earned + $commission, 2),
        ]);

        WalletTransaction::query()->create([
            'wallet_id' => $wallet->id,
            'type' => 'credit',
            'amount' => $commission,
            'balance_before' => $before,
            'balance_after' => $after,
            'transaction_type' => 'platform_commission',
            'reference_type' => $referenceType,
            'reference_id' => $order->id,
            'description' => 'Commission from order '.$order->order_number,
            'metadata' => [
                'restaurant_id' => $order->restaurant_id,
                'commission_rate' => (float) $order->commission_rate,
            ],
            'created_at' => now(),
        ]);
    }
}
