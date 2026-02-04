<?php

namespace Database\Factories;

use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderFactory extends Factory
{
    protected $model = SalesOrder::class;

    public function definition(): array
    {
        $subtotal = $this->faker->numberBetween(1000000, 10000000);
        $tax = $this->faker->numberBetween(100000, 1000000);
        $discount = $this->faker->numberBetween(0, 500000);

        return [
            'so_number' => 'SO-' . date('Ymd') . '-' . $this->faker->unique()->numerify('###'),
            'customer_id' => Customer::factory(),
            'order_date' => $this->faker->dateTime(),
            'required_date' => $this->faker->dateTimeThisMonth(),
            'shipped_date' => null,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $subtotal + $tax - $discount,
            'amount_paid' => 0,
            'status' => $this->faker->randomElement(['draft', 'confirmed', 'shipped', 'delivered', 'cancelled']),
            'created_by' => User::factory(),
            'notes' => $this->faker->optional()->text(),
        ];
    }
}
