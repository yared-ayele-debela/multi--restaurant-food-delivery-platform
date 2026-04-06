<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommissionLedgerController extends Controller
{
    private function baseQuery(Request $request)
    {
        $query = WalletTransaction::query()
            ->with('wallet')
            ->where('transaction_type', 'platform_commission')
            ->whereHas('wallet', function ($walletQuery) {
                $walletQuery->where('holder_type', Setting::class);
            })
            ->latest('created_at');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->string('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->string('date_to'));
        }

        $restaurantId = $request->integer('restaurant_id');
        if ($restaurantId > 0) {
            $query->whereExists(function ($exists) use ($restaurantId) {
                $exists
                    ->select(DB::raw(1))
                    ->from('orders')
                    ->whereColumn('orders.id', 'wallet_transactions.reference_id')
                    ->where('wallet_transactions.reference_type', Order::class)
                    ->where('orders.restaurant_id', $restaurantId);
            });
        }

        return $query;
    }

    public function index(Request $request): View
    {
        $transactions = $this->baseQuery($request)->paginate(20)->withQueryString();

        $orderIds = $transactions->getCollection()
            ->where('reference_type', Order::class)
            ->pluck('reference_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        $orders = Order::query()
            ->whereIn('id', $orderIds)
            ->with('restaurant')
            ->get()
            ->keyBy('id');

        $restaurants = Restaurant::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $summaryQuery = $this->baseQuery($request);

        $summary = [
            'count' => (int) $summaryQuery->count(),
            'total_amount' => (float) $summaryQuery->sum('amount'),
        ];

        return view('admin.commissions.index', compact(
            'transactions',
            'orders',
            'restaurants',
            'summary'
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $transactions = $this->baseQuery($request)->get();

        $orderIds = $transactions
            ->where('reference_type', Order::class)
            ->pluck('reference_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        $orders = Order::query()
            ->whereIn('id', $orderIds)
            ->with('restaurant')
            ->get()
            ->keyBy('id');

        $filename = 'commission-ledger-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($transactions, $orders): void {
            $output = fopen('php://output', 'w');
            if (! $output) {
                return;
            }

            fputcsv($output, [
                'transaction_id',
                'date',
                'order_number',
                'restaurant',
                'commission_amount',
                'balance_before',
                'balance_after',
                'description',
            ]);

            foreach ($transactions as $tx) {
                $order = $tx->reference_type === Order::class
                    ? $orders->get((int) $tx->reference_id)
                    : null;

                fputcsv($output, [
                    $tx->id,
                    $tx->created_at?->format('Y-m-d H:i:s'),
                    $order?->order_number,
                    $order?->restaurant?->name,
                    number_format((float) $tx->amount, 2, '.', ''),
                    number_format((float) $tx->balance_before, 2, '.', ''),
                    number_format((float) $tx->balance_after, 2, '.', ''),
                    $tx->description,
                ]);
            }

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }
}
