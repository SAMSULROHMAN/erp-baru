<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $subtotal = $this->faker->numberBetween(1000000, 10000000);
        $tax = $this->faker->numberBetween(100000, 1000000);
        $discount = $this->faker->numberBetween(0, 500000);

        return [
            'invoice_number' => 'INV-' . date('Ymd') . '-' . $this->faker->unique()->numerify('###'),
            'sales_order_id' => null,
            'customer_id' => Customer::factory(),
            'invoice_date' => $this->faker->dateTime(),
            'due_date' => $this->faker->dateTimeThisMonth(),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $subtotal + $tax - $discount,
            'amount_paid' => 0,
            'status' => $this->faker->randomElement(['draft', 'sent', 'paid', 'overdue', 'cancelled']),
            'created_by' => User::factory(),
            'notes' => $this->faker->optional()->text(),
        ];
    }
}
