<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\RestaurantBranch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductStock>
 */
class ProductStockFactory extends Factory
{
    protected $model = ProductStock::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'branch_id' => null,
            'quantity' => fake()->numberBetween(10, 500),
            'low_stock_threshold' => fake()->numberBetween(5, 20),
            'track_stock' => fake()->boolean(80),
        ];
    }

    public function forBranch(RestaurantBranch $branch): static
    {
        return $this->state(fn (array $attributes) => [
            'branch_id' => $branch->id,
        ]);
    }
}
