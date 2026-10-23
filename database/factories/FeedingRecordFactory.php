<?php

namespace Database\Factories;

use App\Models\Animal;
use App\Models\FeedingRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FeedingRecord> */
class FeedingRecordFactory extends Factory
{
    protected $model = FeedingRecord::class;

    public function definition(): array
    {
        return [
            'animal_id' => Animal::factory(),
            'fed_at' => now(),
            'feed_type' => 'Maize bran',
            'quantity' => 2,
            'unit' => 'kg',
            'recorded_by' => null,
        ];
    }
}
