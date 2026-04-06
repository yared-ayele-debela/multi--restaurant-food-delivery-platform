@extends('admin.layouts.app')
@section('title')
    Dashboard
@endsection
@section('content')
<div class="container-fluid">
    <!-- Page Title -->
    <x-page-title title="Dashboard" :breadcrumbs="[['label' => 'Dashboard']]" />

    <!-- Statistics Cards -->
    <div class="row">
        <!-- Restaurants Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">Total Restaurants</span>
                            <h4 class="mb-3">
                                <span class="counter-value">{{ $stats['total_restaurants'] }}</span>
                            </h4>
                        </div>
                        <div class="col-4 text-end">
                            <div class="avatar-md">
                                <span class="avatar-title rounded-circle bg-primary-subtle text-primary font-size-24">
                                    <i class="mdi mdi-store"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="text-nowrap">
                        <span class="badge bg-success-subtle text-success">{{ $stats['active_restaurants'] }} Active</span>
                        <span class="ms-1 text-muted font-size-13">approved</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">Total Orders</span>
                            <h4 class="mb-3">
                                <span class="counter-value">{{ $stats['total_orders'] }}</span>
                            </h4>
                        </div>
                        <div class="col-4 text-end">
                            <div class="avatar-md">
                                <span class="avatar-title rounded-circle bg-success-subtle text-success font-size-24">
                                    <i class="mdi mdi-cart"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="text-nowrap">
                        <span class="badge bg-warning-subtle text-warning">{{ $stats['pending_orders'] }} Pending</span>
                        <span class="ms-1 text-muted font-size-13">{{ $stats['today_orders'] }} today</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customers Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">Total Customers</span>
                            <h4 class="mb-3">
                                <span class="counter-value">{{ $stats['total_customers'] }}</span>
                            </h4>
                        </div>
                        <div class="col-4 text-end">
                            <div class="avatar-md">
                                <span class="avatar-title rounded-circle bg-info-subtle text-info font-size-24">
                                    <i class="mdi mdi-account-group"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="text-nowrap">
                        <span class="badge bg-info-subtle text-info">{{ $stats['total_drivers'] }} Drivers</span>
                        <span class="ms-1 text-muted font-size-13">approved</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">Today's Revenue</span>
                            <h4 class="mb-3">
                                $<span class="counter-value">{{ number_format($todayRevenue, 2) }}</span>
                            </h4>
                        </div>
                        <div class="col-4 text-end">
                            <div class="avatar-md">
                                <span class="avatar-title rounded-circle bg-warning-subtle text-warning font-size-24">
                                    <i class="mdi mdi-currency-usd"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="text-nowrap">
                        <span class="badge bg-success-subtle text-success">${{ number_format($weekRevenue, 0) }}</span>
                        <span class="ms-1 text-muted font-size-13">this week</span><br>
                        <span class="badge bg-primary-subtle text-primary mt-1">
                            Commission today: ${{ number_format($todayCommissionCollected, 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted mb-2 d-block">Platform Wallet Balance</span>
                            <h4 class="mb-0">${{ number_format($platformWalletBalance, 2) }}</h4>
                        </div>
                        <div class="avatar-md">
                            <span class="avatar-title rounded-circle bg-primary-subtle text-primary font-size-24">
                                <i class="mdi mdi-wallet"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted mb-2 d-block">Total Commission Collected</span>
                            <h4 class="mb-0">${{ number_format($totalCommissionCollected, 2) }}</h4>
                        </div>
                        <div class="avatar-md">
                            <span class="avatar-title rounded-circle bg-info-subtle text-info font-size-24">
                                <i class="mdi mdi-cash-multiple"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Orders Over Time Chart -->
        <div class="col-xl-8">
            <div class="card card-h-100">
                <div class="card-header">
                    <h4 class="card-title mb-0">Orders & Revenue (Last 30 Days)</h4>
                </div>
                <div class="card-body">
                    <div id="orders-chart" data-colors='["#5156be", "#34c38f"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
        </div>

        <!-- Orders by Status Chart -->
        <div class="col-xl-4">
            <div class="card card-h-100">
                <div class="card-header">
                    <h4 class="card-title mb-0">Orders by Status</h4>
                </div>
                <div class="card-body">
                    <div id="status-chart" data-colors='["#5156be", "#34c38f", "#f46a6a", "#f1b44c", "#50a5f1", "#a8aada"]'
                         class="apex-charts" dir="ltr"></div>
                    <div class="mt-4 text-center">
                        <p class="text-muted mb-2">Total Orders: <strong>{{ $stats['total_orders'] }}</strong></p>
                        <p class="text-success mb-0">Completed: <strong>{{ $stats['completed_orders'] }}</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="row">
        <!-- Recent Orders -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Recent Orders</h4>
                    <div class="flex-shrink-0">
                        <a href="#" class="btn btn-soft-primary btn-sm">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Restaurant</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                <tr>
                                    <td><strong>#{{ $order->order_number }}</strong></td>
                                    <td>{{ $order->user?->name ?? 'Guest' }}</td>
                                    <td>{{ $order->restaurant?->name ?? 'N/A' }}</td>
                                    <td>${{ number_format($order->total, 2) }}</td>
                                    <td>
                                        @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'accepted' => 'info',
                                            'preparing' => 'primary',
                                            'ready' => 'success',
                                            'picked_up' => 'secondary',
                                            'on_the_way' => 'info',
                                            'delivered' => 'success',
                                            'completed' => 'dark',
                                            'cancelled' => 'danger',
                                        ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$order->status->value] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $order->status->value)) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No orders yet</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Restaurants -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Top Restaurants</h4>
                    <div class="flex-shrink-0">
                        <a href="#" class="btn btn-soft-primary btn-sm">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Restaurant</th>
                                    <th>Owner</th>
                                    <th>Orders</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topRestaurants as $restaurant)
                                <tr>
                                    <td>
                                        <strong>{{ $restaurant->name }}</strong>
                                        <br><small class="text-muted">{{ $restaurant->city }}</small>
                                    </td>
                                    <td>{{ $restaurant->owner?->name ?? 'N/A' }}</td>
                                    <td><span class="badge bg-primary">{{ $restaurant->orders_count }}</span></td>
                                    <td>
                                        <span class="badge bg-{{ $restaurant->is_active && $restaurant->status === 'approved' ? 'success' : 'warning' }}">
                                            {{ ucfirst($restaurant->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No restaurants yet</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Row -->
    <div class="row">
        <!-- Recent Customers -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Recent Customers</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Customer</th>
                                    <th>Email</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentCustomers as $customer)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3">
                                                <span class="avatar-title rounded-circle bg-primary text-white">
                                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                                </span>
                                            </div>
                                            <strong>{{ $customer->name }}</strong>
                                        </div>
                                    </td>
                                    <td>{{ $customer->email }}</td>
                                    <td>{{ $customer->created_at->diffForHumans() }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No customers yet</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Restaurants -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">New Restaurants</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Restaurant</th>
                                    <th>Owner</th>
                                    <th>Added</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentRestaurants as $restaurant)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3">
                                                <span class="avatar-title rounded-circle bg-success text-white">
                                                    <i class="mdi mdi-store"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <strong>{{ $restaurant->name }}</strong>
                                                <br><small class="text-muted">{{ $restaurant->city }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $restaurant->owner?->name ?? 'N/A' }}</td>
                                    <td>{{ $restaurant->created_at->diffForHumans() }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No restaurants yet</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart Scripts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Orders Over Time Chart
        var ordersOptions = {
            series: [{
                name: 'Orders',
                data: {!! json_encode($chartData['orders'] ?? []) !!}
            }, {
                name: 'Revenue',
                data: {!! json_encode($chartData['revenue'] ?? []) !!}
            }],
            chart: {
                height: 350,
                type: 'area',
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            xaxis: {
                type: 'datetime',
                categories: {!! json_encode($chartData['dates'] ?? []) !!}
            },
            yaxis: [{
                title: {
                    text: 'Orders',
                },
            }, {
                opposite: true,
                title: {
                    text: 'Revenue ($)'
                }
            }],
            colors: ['#5156be', '#34c38f'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.2,
                    stops: [0, 90, 100]
                }
            },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function(y, { seriesIndex }) {
                        if (typeof y !== "undefined") {
                            return seriesIndex === 0 ? y.toFixed(0) + " orders" : "$" + y.toFixed(2);
                        }
                        return y;
                    }
                }
            },
            noData: {
                text: 'No orders data available'
            }
        };

        var ordersChart = new ApexCharts(document.querySelector("#orders-chart"), ordersOptions);
        ordersChart.render();

        // Orders by Status Chart
        var statusLabels = {!! json_encode(array_map(function($s) { return ucfirst(str_replace('_', ' ', $s)); }, array_keys($ordersByStatus))) !!};
        var statusSeries = {!! json_encode(array_values($ordersByStatus)) !!};

        var statusOptions = {
            series: statusSeries,
            labels: statusLabels,
            chart: {
                height: 320,
                type: 'donut',
            },
            colors: ['#f1b44c', '#50a5f1', '#5156be', '#34c38f', '#a8aada', '#2b3940', '#f46a6a'],
            legend: {
                position: 'bottom'
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%'
                    }
                }
            },
            noData: {
                text: 'No status data available'
            }
        };

        var statusChart = new ApexCharts(document.querySelector("#status-chart"), statusOptions);
        statusChart.render();
    });
</script>
@endsection
