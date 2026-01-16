<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\RestaurantBranch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $restaurant = $request->attributes->get('restaurant');

        // Authorize using policy
        $this->authorize('manageMenu', $restaurant);

        $query = Product::where('restaurant_id', $restaurant->id)
            ->with(['category', 'sizes', 'addons', 'stock']);

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Filter by status
        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by featured
        if ($request->has('is_featured') && $request->input('is_featured') !== '') {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        // Search
        if ($request->filled('q')) {
            $q = '%'.$request->string('q').'%';
            $query->where(function ($qry) use ($q) {
                $qry->where('name', 'like', $q)
                    ->orWhere('description', 'like', $q);
            });
        }

        $products = $query->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('restaurant.products.index', [
            'products' => $products,
            'categories' => $categories,
            'restaurant' => $restaurant,
        ]);
    }

    public function create(Request $request): View
    {
        $restaurant = $request->attributes->get('restaurant');

        // Authorize using policy
        $this->authorize('manageMenu', $restaurant);

        $categories = Category::where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $branches = RestaurantBranch::where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('restaurant.products.create', [
            'restaurant' => $restaurant,
            'categories' => $categories,
            'branches' => $branches,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $restaurant = $request->attributes->get('restaurant');

        // Authorize using policy
        $this->authorize('manageMenu', $restaurant);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lt:base_price'],
            'preparation_time' => ['nullable', 'integer', 'min:1'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'dietary_info' => ['nullable', 'array'],
            'allergens' => ['nullable', 'array'],
            'calories' => ['nullable', 'integer', 'min:0'],
        ]);

        // Verify category belongs to restaurant
        $category = Category::findOrFail($validated['category_id']);
        if ($category->restaurant_id !== $restaurant->id) {
            return back()->with('error', 'Invalid category selected.');
        }

        $validated['restaurant_id'] = $restaurant->id;
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured', false);

        if (empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);

        return redirect()->route('restaurant.products.edit', $product)
            ->with('success', 'Product created successfully. You can now add sizes, addons, and stock.');
    }

    public function edit(Request $request, Product $product): View
    {
        $restaurant = $request->attributes->get('restaurant');
        
        // Authorize using policy
        $this->authorize('manageMenu', $restaurant);

        $categories = Category::where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $branches = RestaurantBranch::where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $product->load(['sizes', 'addons', 'stock.branch']);

        return view('restaurant.products.edit', [
            'product' => $product,
            'restaurant' => $restaurant,
            'categories' => $categories,
            'branches' => $branches,
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $restaurant = $request->attributes->get('restaurant');
        
        // Authorize using policy
        $this->authorize('manageMenu', $restaurant);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug,'.$product->id],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lt:base_price'],
            'preparation_time' => ['nullable', 'integer', 'min:1'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'dietary_info' => ['nullable', 'array'],
            'allergens' => ['nullable', 'array'],
            'calories' => ['nullable', 'integer', 'min:0'],
        ]);

        // Verify category belongs to restaurant
        $category = Category::findOrFail($validated['category_id']);
        if ($category->restaurant_id !== $restaurant->id) {
            return back()->with('error', 'Invalid category selected.');
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured', false);

        if (empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        if ($request->hasFile('image')) {
            if ($product->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return redirect()->route('restaurant.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $restaurant = $request->attributes->get('restaurant');
        
        // Authorize using policy
        $this->authorize('manageMenu', $restaurant);

        // Delete image if exists
        if ($product->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('restaurant.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    private function authorizeAccess($restaurant, Product $product): void
    {
        if ($product->restaurant_id !== $restaurant->id) {
            abort(403, 'Unauthorized access to this product.');
        }
    }
}
