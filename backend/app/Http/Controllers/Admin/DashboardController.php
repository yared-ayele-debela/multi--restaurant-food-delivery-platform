<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Statistics Cards
        $stats = [
            'total_restaurants' => Restaurant::count(),
            'active_restaurants' => Restaurant::where('is_active', true)->where('status', 'approved')->count(),
            'total_orders' => Order::count(),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'total_customers' => User::whereHas('roles', fn($q) => $q->where('name', 'customer'))->count(),
            'total_drivers' => Driver::where('is_approved', true)->count(),
            'pending_orders' => Order::where('status', OrderStatus::Pending->value)->count(),
            'completed_orders' => Order::where('status', OrderStatus::Completed->value)->count(),
        ];

        // Financial Stats
        $todayRevenue = Order::whereDate('created_at', today())
            ->where('payment_status', 'paid')
            ->sum('total');

        $weekRevenue = Order::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('payment_status', 'paid')
            ->sum('total');

        $monthRevenue = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('payment_status', 'paid')
            ->sum('total');

        // Orders by Status for Chart
        $ordersByStatus = Order::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Orders Over Time (Last 30 Days) for Chart
        $ordersOverTime = Order::selectRaw('DATE(created_at) as date, count(*) as count, sum(total) as revenue')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $chartData = [
            'dates' => $ordersOverTime->pluck('date')->toArray(),
            'orders' => $ordersOverTime->pluck('count')->toArray(),
            'revenue' => $ordersOverTime->pluck('revenue')->map(fn($v) => round($v, 2))->toArray(),
        ];

        // Recent Orders
        $recentOrders = Order::with(['user', 'restaurant'])
            ->latest()
            ->limit(10)
            ->get();

        // Recent Restaurants
        $recentRestaurants = Restaurant::with('owner')
            ->latest()
            ->limit(5)
            ->get();

        // Top Restaurants by Order Count
        $topRestaurants = Restaurant::withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->limit(5)
            ->get();

        // Recent Customers
        $recentCustomers = User::whereHas('roles', fn($q) => $q->where('name', 'customer'))
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'todayRevenue',
            'weekRevenue',
            'monthRevenue',
            'ordersByStatus',
            'chartData',
            'recentOrders',
            'recentRestaurants',
            'topRestaurants',
            'recentCustomers'
        ));
    }
}
