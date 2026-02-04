<?php

namespace Database\Factories;

use App\Models\SalesOrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SalesOrderItem>
 */
class SalesOrderItemFactory extends Factory
{
    protected $model = SalesOrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 50);
        $unitPrice = fake()->numberBetween(50000, 1000000);

        return [
            'product_id' => Product::inRandomOrder()->first()?->id ?? Product::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $quantity * $unitPrice,
            'quantity_shipped' => 0,
        ];
    }
}
