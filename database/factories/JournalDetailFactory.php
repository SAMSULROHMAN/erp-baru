<?php

namespace Database\Factories;

use App\Models\JournalDetail;
use App\Models\Journal;
use App\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class JournalDetailFactory extends Factory
{
    protected $model = JournalDetail::class;

    public function definition(): array
    {
        return [
            'journal_id' => Journal::factory(),
            'chart_of_account_id' => ChartOfAccount::factory(),
            'debit' => $this->faker->randomElement([0, $this->faker->numberBetween(100000, 1000000)]),
            'credit' => 0,
            'description' => $this->faker->optional()->sentence(),
            'reference_type' => null,
            'reference_number' => null,
        ];
    }
}
