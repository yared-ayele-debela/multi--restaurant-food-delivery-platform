@extends('admin.layouts.app')
@section('title')
    Commission Ledger
@endsection
@section('content')
<div class="container-fluid">
    <x-page-title
        title="Admin Commission Ledger"
        :breadcrumbs="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Commission Ledger'],
        ]"
    />

    <x-alert />

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Transactions</p>
                    <h4 class="mb-0">{{ number_format($summary['count']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Commission</p>
                    <h4 class="mb-0">${{ number_format($summary['total_amount'], 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="get" action="{{ route('admin.commissions.index') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-3">
                    <label class="form-label">Restaurant</label>
                    <select name="restaurant_id" class="form-select">
                        <option value="">All restaurants</option>
                        @foreach($restaurants as $restaurant)
                            <option value="{{ $restaurant->id }}" @selected((string) request('restaurant_id') === (string) $restaurant->id)>
                                {{ $restaurant->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date from</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date to</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.commissions.index') }}" class="btn btn-soft-secondary">Reset</a>
                    <a
                        href="{{ route('admin.commissions.export-csv', request()->query()) }}"
                        class="btn btn-success"
                    >
                        Export CSV
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Order</th>
                        <th>Restaurant</th>
                        <th>Commission</th>
                        <th>Wallet Balance After</th>
                        <th>Description</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($transactions as $tx)
                        @php
                            $order = $tx->reference_type === \App\Models\Order::class
                                ? $orders->get((int) $tx->reference_id)
                                : null;
                        @endphp
                        <tr>
                            <td>#{{ $tx->id }}</td>
                            <td>{{ $tx->created_at?->format('Y-m-d H:i') }}</td>
                            <td>
                                @if($order)
                                    #{{ $order->order_number }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ $order?->restaurant?->name ?? 'N/A' }}</td>
                            <td>${{ number_format((float) $tx->amount, 2) }}</td>
                            <td>${{ number_format((float) $tx->balance_after, 2) }}</td>
                            <td class="small text-muted">{{ $tx->description ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No commission transactions found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
