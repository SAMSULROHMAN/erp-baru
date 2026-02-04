<?php

namespace Database\Factories;

use App\Models\BomItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class BomItemFactory extends Factory
{
    protected $model = BomItem::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'material_product_id' => Product::factory(),
            'quantity_required' => $this->faker->numberBetween(1, 10),
            'unit' => $this->faker->randomElement(['pcs', 'box', 'kg', 'ltr']),
            'estimated_cost' => $this->faker->numberBetween(10000, 100000),
        ];
    }
}
