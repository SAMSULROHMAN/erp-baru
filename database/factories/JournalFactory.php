<?php

namespace Database\Factories;

use App\Models\Journal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JournalFactory extends Factory
{
    protected $model = Journal::class;

    public function definition(): array
    {
        return [
            'journal_number' => 'J-' . date('Ymd') . '-' . $this->faker->unique()->numerify('###'),
            'type' => $this->faker->randomElement(['general', 'sales', 'purchase', 'cash']),
            'journal_date' => $this->faker->dateTime(),
            'description' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement(['draft', 'posted', 'reversed']),
            'created_by' => User::factory(),
            'posted_by' => null,
            'posted_at' => null,
            'notes' => $this->faker->optional()->text(),
        ];
    }
}
