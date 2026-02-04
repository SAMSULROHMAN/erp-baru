<?php

namespace Database\Factories;

use App\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChartOfAccountFactory extends Factory
{
    protected $model = ChartOfAccount::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->numerify('####'),
            'name' => $this->faker->word(),
            'type' => $this->faker->randomElement(['asset', 'liability', 'equity', 'income', 'expense']),
            'sub_type' => $this->faker->optional()->randomElement(['current', 'fixed', 'other']),
            'balance' => $this->faker->numberBetween(0, 10000000),
            'description' => $this->faker->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
