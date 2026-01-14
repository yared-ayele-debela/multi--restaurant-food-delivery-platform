<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductSize>
 */
class ProductSizeFactory extends Factory
{
    protected $model = ProductSize::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => fake()->randomElement(['Small', 'Medium', 'Large', 'Regular', 'Family Size']),
            'price' => fake()->randomFloat(2, 3, 50),
            'is_default' => false,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
