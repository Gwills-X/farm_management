<?php

namespace Database\Factories;

use App\Models\Animal;
use App\Models\AnimalType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Animal> */
class AnimalFactory extends Factory
{
    protected $model = Animal::class;

    public function definition(): array
    {
        return [
            'animal_type_id' => AnimalType::factory(),
            // Codes produced by the factory bypass the sequence generator; they only need to be unique.
            'animal_code' => function (array $attributes) {
                $prefix = AnimalType::query()->find($attributes['animal_type_id'])?->code_prefix ?? 'TST';

                return sprintf('%s-%05d', $prefix, fake()->unique()->numberBetween(1, 99999));
            },
            'name' => fake()->firstName(),
            'sex' => 'unknown',
            'acquisition_type' => 'born_on_farm',
            'acquisition_date' => now()->subMonths(2)->toDateString(),
            'status' => Animal::STATUS_ACTIVE,
        ];
    }

    public function sold(): static
    {
        return $this->state(fn () => ['status' => Animal::STATUS_SOLD]);
    }

    public function deceased(): static
    {
        return $this->state(fn () => ['status' => Animal::STATUS_DECEASED]);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => Animal::STATUS_ARCHIVED]);
    }
}
