<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AdminCompleteWithdrawalRequest;
use App\Http\Requests\Api\AdminRejectWithdrawalRequest;
use App\Http\Resources\WithdrawalRequestAdminResource;
use App\Models\WithdrawalRequest;
use App\Services\WithdrawalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WithdrawalController extends Controller
{
    public function __construct(
        private WithdrawalService $withdrawalService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $withdrawals = WithdrawalRequest::query()
            ->with('wallet')
            ->latest()
            ->paginate(20);

        return WithdrawalRequestAdminResource::collection($withdrawals);
    }

    public function complete(AdminCompleteWithdrawalRequest $request, WithdrawalRequest $withdrawal): JsonResponse
    {
        $this->authorize('complete', $withdrawal);

        $withdrawal = $this->withdrawalService->completeWithdrawal(
            $withdrawal,
            $request->user(),
            $request->validated('admin_notes')
        );

        return (new WithdrawalRequestAdminResource($withdrawal->load('wallet')))->response();
    }

    public function reject(AdminRejectWithdrawalRequest $request, WithdrawalRequest $withdrawal): JsonResponse
    {
        $this->authorize('reject', $withdrawal);

        $withdrawal = $this->withdrawalService->rejectWithdrawal(
            $withdrawal,
            $request->user(),
            $request->validated('rejection_reason'),
            $request->validated('admin_notes')
        );

        return (new WithdrawalRequestAdminResource($withdrawal->load('wallet')))->response();
    }
}
