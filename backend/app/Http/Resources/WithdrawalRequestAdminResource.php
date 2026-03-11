<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\WithdrawalRequest */
class WithdrawalRequestAdminResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wallet_id' => $this->wallet_id,
            'holder_type' => $this->whenLoaded('wallet', fn () => $this->wallet?->holder_type),
            'holder_id' => $this->whenLoaded('wallet', fn () => $this->wallet?->holder_id),
            'amount' => (string) $this->amount,
            'status' => $this->status,
            'bank_name' => $this->bank_name,
            'account_number' => $this->account_number,
            'account_holder_name' => $this->account_holder_name,
            'payment_method' => $this->payment_method,
            'rejection_reason' => $this->rejection_reason,
            'admin_notes' => $this->admin_notes,
            'processed_at' => $this->processed_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'rejected_at' => $this->rejected_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
