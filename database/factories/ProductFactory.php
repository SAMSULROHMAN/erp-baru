<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'code' => 'PROD-' . $this->faker->unique()->numerify('####'),
            'name' => $this->faker->word(),
            'description' => $this->faker->optional()->sentence(),
            'category_id' => Category::factory(),
            'cost_price' => $this->faker->numberBetween(10000, 100000),
            'selling_price' => $this->faker->numberBetween(100000, 500000),
            'stock_quantity' => $this->faker->numberBetween(0, 1000),
            'reorder_level' => $this->faker->numberBetween(5, 50),
            'unit' => $this->faker->randomElement(['pcs', 'box', 'kg', 'ltr']),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
}
