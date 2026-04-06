<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RestaurantController extends Controller
{
    public function index(Request $request): View
    {
        $query = Restaurant::query()->with('owner');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('q')) {
            $q = '%'.$request->string('q').'%';
            $query->where(function ($qry) use ($q) {
                $qry->where('name', 'like', $q)
                    ->orWhere('city', 'like', $q)
                    ->orWhere('slug', 'like', $q);
            });
        }

        if ($request->has('featured') && $request->input('featured') !== '') {
            $query->where('is_featured', (bool) (int) $request->input('featured'));
        }

        $restaurants = $query->latest()->paginate(20)->withQueryString();

        return view('admin.restaurants.index', [
            'restaurants' => $restaurants,
            'statuses' => [
                Restaurant::STATUS_PENDING,
                Restaurant::STATUS_APPROVED,
                Restaurant::STATUS_REJECTED,
                Restaurant::STATUS_SUSPENDED,
            ],
        ]);
    }

    public function show(Restaurant $restaurant): View
    {
        $restaurant->load(['owner', 'images' => function ($query) {
            $query->orderByDesc('is_primary')->orderBy('sort_order')->orderBy('id');
        }, 'branches', 'hours', 'categories' => function ($query) {
            $query->withCount('products');
        }]);

        $stats = [
            'total_orders' => $restaurant->orders()->count(),
            'total_revenue' => $restaurant->orders()->where('payment_status', 'paid')->sum('total'),
            'total_products' => $restaurant->products()->count(),
            'total_categories' => $restaurant->categories()->count(),
        ];

        return view('admin.restaurants.show', compact('restaurant', 'stats'));
    }

    public function approve(Restaurant $restaurant): RedirectResponse
    {
        $restaurant->update([
            'status' => Restaurant::STATUS_APPROVED,
            'is_active' => true,
        ]);

        return back()->with('success', 'Restaurant approved.');
    }

    public function reject(Restaurant $restaurant): RedirectResponse
    {
        $restaurant->update([
            'status' => Restaurant::STATUS_REJECTED,
            'is_active' => false,
        ]);

        return back()->with('success', 'Restaurant rejected.');
    }

    public function suspend(Restaurant $restaurant): RedirectResponse
    {
        $restaurant->update([
            'status' => Restaurant::STATUS_SUSPENDED,
            'is_active' => false,
        ]);

        return back()->with('success', 'Restaurant suspended.');
    }

    public function toggleFeatured(Restaurant $restaurant): RedirectResponse
    {
        $restaurant->update([
            'is_featured' => ! $restaurant->is_featured,
        ]);

        return back()->with('success', $restaurant->is_featured ? 'Restaurant marked as featured.' : 'Restaurant unfeatured.');
    }
}
