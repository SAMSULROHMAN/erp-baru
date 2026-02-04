<?php

namespace Database\Factories;

use App\Models\PurchaseOrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseOrderItem>
 */
class PurchaseOrderItemFactory extends Factory
{
    protected $model = PurchaseOrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(5, 100);
        $unitPrice = fake()->numberBetween(10000, 500000);

        return [
            'product_id' => Product::inRandomOrder()->first()?->id ?? Product::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $quantity * $unitPrice,
            'received_quantity' => 0,
        ];
    }
}
