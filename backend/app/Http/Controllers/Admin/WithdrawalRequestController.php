<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Services\WithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WithdrawalRequestController extends Controller
{
    public function __construct(
        private WithdrawalService $withdrawalService
    ) {}

    public function index(Request $request): View
    {
        $query = WithdrawalRequest::query()
            ->with(['wallet.holder', 'processedBy'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $withdrawals = $query->paginate(20)->withQueryString();

        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    public function complete(Request $request, WithdrawalRequest $withdrawalRequest): RedirectResponse
    {
        $this->authorize('complete', $withdrawalRequest);

        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->withdrawalService->completeWithdrawal(
            $withdrawalRequest,
            $request->user(),
            $validated['admin_notes'] ?? null
        );

        return back()->with('success', 'Withdrawal marked completed.');
    }

    public function reject(Request $request, WithdrawalRequest $withdrawalRequest): RedirectResponse
    {
        $this->authorize('reject', $withdrawalRequest);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->withdrawalService->rejectWithdrawal(
            $withdrawalRequest,
            $request->user(),
            $validated['rejection_reason'],
            $validated['admin_notes'] ?? null
        );

        return back()->with('success', 'Withdrawal rejected.');
    }
}
