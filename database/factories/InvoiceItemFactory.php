<?php

namespace Database\Factories;

use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

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
        ];
    }
}
