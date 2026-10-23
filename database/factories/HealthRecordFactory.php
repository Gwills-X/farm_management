<?php

namespace Database\Factories;

use App\Models\Animal;
use App\Models\HealthRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<HealthRecord> */
class HealthRecordFactory extends Factory
{
    protected $model = HealthRecord::class;

    public function definition(): array
    {
        return [
            'animal_id' => Animal::factory(),
            'observation_date' => now()->toDateString(),
            'observed_condition' => fake()->sentence(),
            'health_status' => 'healthy',
            'recorded_by' => null,
        ];
    }
}
