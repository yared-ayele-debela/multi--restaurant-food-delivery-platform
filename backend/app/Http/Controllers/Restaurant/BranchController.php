<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\RestaurantBranch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(Request $request): View
    {
        $restaurant = $request->attributes->get('restaurant');

        // Authorize using policy
        $this->authorize('manageBranches', $restaurant);

        $branches = RestaurantBranch::where('restaurant_id', $restaurant->id)
            ->orderBy('name')
            ->paginate(20);

        return view('restaurant.branches.index', [
            'branches' => $branches,
            'restaurant' => $restaurant,
        ]);
    }

    public function create(Request $request): View
    {
        $restaurant = $request->attributes->get('restaurant');

        // Authorize using policy
        $this->authorize('manageBranches', $restaurant);

        return view('restaurant.branches.create', [
            'restaurant' => $restaurant,
            'geoapifyKey' => (string) config('services.geoapify.key', ''),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $restaurant = $request->attributes->get('restaurant');

        // Authorize using policy
        $this->authorize('manageBranches', $restaurant);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'phone' => ['nullable', 'string', 'max:20'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'delivery_radius' => ['nullable', 'numeric', 'min:0'],
            'preparation_time' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]);

        $validated['restaurant_id'] = $restaurant->id;
        $validated['is_active'] = $request->boolean('is_active', true);
        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('branches/images', 'public');
        }

        RestaurantBranch::create($validated);

        return redirect()->route('restaurant.branches.index')
            ->with('success', 'Branch created successfully.');
    }

    public function edit(Request $request, RestaurantBranch $branch): View
    {
        $restaurant = $request->attributes->get('restaurant');
        
        // Authorize using policy
        $this->authorize('updateBranch', $branch);

        return view('restaurant.branches.edit', [
            'branch' => $branch,
            'restaurant' => $restaurant,
            'geoapifyKey' => (string) config('services.geoapify.key', ''),
        ]);
    }

    public function update(Request $request, RestaurantBranch $branch): RedirectResponse
    {
        $restaurant = $request->attributes->get('restaurant');
        
        // Authorize using policy
        $this->authorize('updateBranch', $branch);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'phone' => ['nullable', 'string', 'max:20'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
            'delivery_radius' => ['nullable', 'numeric', 'min:0'],
            'preparation_time' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        if ($request->boolean('remove_image') && $branch->image_path) {
            Storage::disk('public')->delete($branch->image_path);
            $validated['image_path'] = null;
        }
        if ($request->hasFile('image')) {
            if ($branch->image_path) {
                Storage::disk('public')->delete($branch->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('branches/images', 'public');
        }

        $branch->update($validated);

        return redirect()->route('restaurant.branches.index')
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy(Request $request, RestaurantBranch $branch): RedirectResponse
    {
        $restaurant = $request->attributes->get('restaurant');
        
        // Authorize using policy
        $this->authorize('updateBranch', $branch);

        if ($branch->image_path) {
            Storage::disk('public')->delete($branch->image_path);
        }
        $branch->delete();

        return redirect()->route('restaurant.branches.index')
            ->with('success', 'Branch deleted successfully.');
    }

    private function authorizeAccess($restaurant, RestaurantBranch $branch): void
    {
        if ($branch->restaurant_id !== $restaurant->id) {
            abort(403, 'Unauthorized access to this branch.');
        }
    }
}
