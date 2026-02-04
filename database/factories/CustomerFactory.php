<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'country' => $this->faker->country(),
            'credit_limit' => $this->faker->numberBetween(5000000, 50000000),
            'credit_used' => 0,
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'notes' => $this->faker->optional()->text(),
        ];
    }
}
