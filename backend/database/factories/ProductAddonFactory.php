<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductAddon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductAddon>
 */
class ProductAddonFactory extends Factory
{
    protected $model = ProductAddon::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => fake()->words(2, true),
            'price' => fake()->randomFloat(2, 0.5, 5),
            'is_active' => true,
            'max_quantity' => fake()->numberBetween(1, 5),
            'group_name' => fake()->randomElement(['Extras', 'Toppings', 'Sauces', 'Sides', null]),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
