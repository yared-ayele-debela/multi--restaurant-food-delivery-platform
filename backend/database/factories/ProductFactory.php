<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\ProductStock;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'restaurant_id' => Restaurant::factory(),
            'name' => $name,
            'slug' => Str::slug($name.'-'.fake()->unique()->numerify('####')),
            'description' => fake()->optional()->sentence(),
            'image' => null,
            'base_price' => fake()->randomFloat(2, 3, 45),
            'discount_price' => null,
            'preparation_time' => null,
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => fake()->numberBetween(0, 100),
            'dietary_info' => null,
            'allergens' => null,
            'calories' => null,
        ];
    }

    public function configure(): static
    {
        return $this
            ->afterMaking(function (Product $product) {
                $category = Category::query()->where('restaurant_id', $product->restaurant_id)->first()
                    ?? Category::factory()->create(['restaurant_id' => $product->restaurant_id]);
                $product->category_id = $category->id;
            })
            ->afterCreating(function (Product $product) {
                if (ProductSize::query()->where('product_id', $product->id)->exists()) {
                    return;
                }
                ProductSize::query()->create([
                    'product_id' => $product->id,
                    'name' => 'Standard',
                    'price' => $product->base_price,
                    'is_default' => true,
                    'sort_order' => 0,
                ]);
                ProductStock::query()->create([
                    'product_id' => $product->id,
                    'branch_id' => null,
                    'quantity' => 999,
                    'low_stock_threshold' => 5,
                    'track_stock' => false,
                ]);
            });
    }

    public function forRestaurant(Restaurant $restaurant): static
    {
        return $this->state(fn (array $attributes) => [
            'restaurant_id' => $restaurant->id,
        ]);
    }
}
