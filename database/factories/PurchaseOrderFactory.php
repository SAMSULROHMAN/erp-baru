<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'po_number' => 'PO-' . date('Ymd') . '-' . $this->faker->unique()->numerify('###'),
            'supplier_id' => Supplier::factory(),
            'order_date' => $this->faker->dateTime(),
            'expected_delivery_date' => $this->faker->dateTimeThisMonth(),
            'delivery_date' => null,
            'subtotal' => $this->faker->numberBetween(1000000, 10000000),
            'tax' => $this->faker->numberBetween(100000, 1000000),
            'total' => 0,
            'status' => $this->faker->randomElement(['draft', 'submitted', 'received', 'cancelled']),
            'created_by' => User::factory(),
            'notes' => $this->faker->optional()->text(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (PurchaseOrder $po) {
            $po->update(['total' => $po->subtotal + $po->tax]);
        });
    }
}
