<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\RestaurantImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(Request $request): View
    {
        $restaurant = $request->attributes->get('restaurant');

        $this->authorize('manage', $restaurant);

        $restaurant->load(['images' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')]);

        return view('restaurant.settings.edit', [
            'restaurant' => $restaurant,
            'geoapifyKey' => (string) config('services.geoapify.key', ''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $restaurant = $request->attributes->get('restaurant');

        $this->authorize('manage', $restaurant);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'phone' => ['required', 'string', 'max:32'],
            'address_line' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'size:2'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'delivery_fee' => ['required', 'numeric', 'min:0'],
            'minimum_order_amount' => ['required', 'numeric', 'min:0'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'delete_image_ids' => ['nullable', 'array'],
            'delete_image_ids.*' => ['integer', 'exists:restaurant_images,id'],
            'primary_image_id' => ['nullable', 'integer', 'exists:restaurant_images,id'],
        ]);

        DB::transaction(function () use ($request, $restaurant, $validated): void {
            $newSlug = Str::slug($validated['name']);
            if ($newSlug !== $restaurant->slug) {
                $suffix = 1;
                $baseSlug = $newSlug;
                while (
                    \App\Models\Restaurant::where('slug', $newSlug)
                        ->whereKeyNot($restaurant->id)
                        ->exists()
                ) {
                    $newSlug = $baseSlug.'-'.$suffix;
                    $suffix++;
                }
            }

            $restaurant->update([
                'name' => $validated['name'],
                'slug' => $newSlug,
                'description' => $validated['description'] ?? null,
                'phone' => $validated['phone'],
                'address_line' => $validated['address_line'],
                'city' => $validated['city'],
                'postal_code' => $validated['postal_code'] ?? null,
                'country' => strtoupper($validated['country']),
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'delivery_fee' => $validated['delivery_fee'],
                'minimum_order_amount' => $validated['minimum_order_amount'],
            ]);

            $existingImages = $restaurant->images()->orderBy('sort_order')->orderBy('id')->get();
            $existingCount = $existingImages->count();

            $deleteIds = collect($validated['delete_image_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->values();

            if ($deleteIds->isNotEmpty()) {
                $imagesToDelete = $restaurant->images()->whereIn('id', $deleteIds)->get();
                foreach ($imagesToDelete as $image) {
                    if ($image->image_path) {
                        Storage::disk('public')->delete($image->image_path);
                    }
                    $image->delete();
                }
                $existingCount -= $imagesToDelete->count();
            }

            $uploadedFiles = collect($request->file('images', []))->filter();
            if ($existingCount + $uploadedFiles->count() > 5) {
                throw ValidationException::withMessages([
                    'images' => 'You can have a maximum of 5 restaurant images.',
                ]);
            }

            $nextSortOrder = (int) ($restaurant->images()->max('sort_order') ?? 0) + 1;
            foreach ($uploadedFiles as $file) {
                $path = $file->store('restaurants/images', 'public');
                RestaurantImage::create([
                    'restaurant_id' => $restaurant->id,
                    'image_path' => $path,
                    'alt_text' => $restaurant->name,
                    'sort_order' => $nextSortOrder++,
                    'is_primary' => false,
                ]);
            }

            $finalImages = $restaurant->images()->orderBy('sort_order')->orderBy('id')->get();
            $primaryImageId = isset($validated['primary_image_id']) ? (int) $validated['primary_image_id'] : null;
            if ($primaryImageId && $finalImages->contains('id', $primaryImageId)) {
                $restaurant->images()->update(['is_primary' => false]);
                $restaurant->images()->whereKey($primaryImageId)->update(['is_primary' => true]);
            } elseif ($finalImages->isNotEmpty() && ! $finalImages->contains('is_primary', true)) {
                $restaurant->images()->whereKey($finalImages->first()->id)->update(['is_primary' => true]);
            }
        });

        return redirect()->route('restaurant.settings.edit')
            ->with('success', 'Restaurant profile settings updated successfully.');
    }
}
