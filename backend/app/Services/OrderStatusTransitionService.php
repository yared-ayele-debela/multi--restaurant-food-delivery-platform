<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderStatusTransitionService
{
    public function __construct(
        private OrderDeliveryService $orderDeliveryService,
        private RestaurantWalletService $restaurantWalletService,
        private PlatformWalletService $platformWalletService,
        private LoyaltyService $loyaltyService,
    ) {}

    /**
     * Allowed next statuses from each current status (linear fulfillment + terminal states).
     *
     * @var array<string, list<OrderStatus>>
     */
    private const array TRANSITIONS = [
        'pending' => [OrderStatus::Accepted, OrderStatus::Cancelled],
        'accepted' => [OrderStatus::Preparing, OrderStatus::Cancelled],
        'preparing' => [OrderStatus::Ready, OrderStatus::Cancelled],
        'ready' => [OrderStatus::PickedUp, OrderStatus::OnTheWay, OrderStatus::Cancelled],
        'picked_up' => [OrderStatus::OnTheWay, OrderStatus::Delivered],
        'on_the_way' => [OrderStatus::Delivered],
        'delivered' => [OrderStatus::Completed],
        'completed' => [OrderStatus::Refunded],
        'cancelled' => [],
        'refunded' => [],
    ];

    /**
     * @param  array<string, mixed>  $context  Optional: cancellation_reason when cancelling
     */
    public function transition(Order $order, OrderStatus $to, ?User $actor = null, ?string $notes = null, array $context = []): Order
    {
        $from = $order->status;

        if ($from === $to) {
            return $order;
        }

        if (! $this->isAllowed($from, $to)) {
            throw ValidationException::withMessages([
                'status' => [
                    sprintf(
                        'Cannot change status from %s to %s.',
                        $from->value,
                        $to->value
                    ),
                ],
            ]);
        }

        if ($to === OrderStatus::Cancelled && empty(trim((string) ($context['cancellation_reason'] ?? '')))) {
            throw ValidationException::withMessages([
                'cancellation_reason' => ['A cancellation reason is required.'],
            ]);
        }

        return DB::transaction(function () use ($order, $from, $to, $actor, $notes, $context) {
            $updates = [
                'status' => $to,
            ];

            $ts = $this->timestampAttributeForStatus($to);
            if ($ts !== null) {
                $updates[$ts] = now();
            }

            if ($to === OrderStatus::Cancelled) {
                $updates['cancellation_reason'] = $context['cancellation_reason'] ?? null;
                $updates['cancelled_by'] = $actor?->id;
            }

            $order->update($updates);

            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'previous_status' => $from->value,
                'new_status' => $to->value,
                'changed_by' => $actor?->id,
                'notes' => $notes,
                'created_at' => now(),
            ]);

            $order->refresh();

            if ($to === OrderStatus::Ready) {
                $this->orderDeliveryService->ensureForOrder($order);
            }

            if ($to === OrderStatus::Completed) {
                $completed = $order->fresh(['user']);
                $this->restaurantWalletService->creditForCompletedOrder($completed);
                $this->platformWalletService->creditCommissionForCompletedOrder($completed);
                $this->loyaltyService->accrueForCompletedOrder($completed);
            }

            return $order->fresh();
        });
    }

    public function isAllowed(OrderStatus $from, OrderStatus $to): bool
    {
        $next = self::TRANSITIONS[$from->value] ?? [];

        return in_array($to, $next, true);
    }

    private function timestampAttributeForStatus(OrderStatus $status): ?string
    {
        return match ($status) {
            OrderStatus::Accepted => 'accepted_at',
            OrderStatus::Preparing => 'preparing_at',
            OrderStatus::Ready => 'ready_at',
            OrderStatus::PickedUp => 'picked_up_at',
            OrderStatus::Delivered => 'delivered_at',
            OrderStatus::Completed => 'completed_at',
            OrderStatus::Cancelled => 'cancelled_at',
            default => null,
        };
    }
}
