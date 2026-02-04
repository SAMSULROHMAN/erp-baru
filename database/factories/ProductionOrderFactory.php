<?php

namespace Database\Factories;

use App\Models\ProductionOrder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductionOrderFactory extends Factory
{
    protected $model = ProductionOrder::class;

    public function definition(): array
    {
        return [
            'pro_number' => 'PRO-' . date('Ymd') . '-' . $this->faker->unique()->numerify('###'),
            'product_id' => Product::factory(),
            'quantity' => $this->faker->numberBetween(10, 1000),
            'quantity_produced' => 0,
            'start_date' => $this->faker->dateTime(),
            'scheduled_end_date' => $this->faker->dateTimeThisMonth(),
            'actual_end_date' => null,
            'status' => $this->faker->randomElement(['draft', 'scheduled', 'in_progress', 'completed', 'cancelled']),
            'created_by' => User::factory(),
            'notes' => $this->faker->optional()->text(),
        ];
    }
}
