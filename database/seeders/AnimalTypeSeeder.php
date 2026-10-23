<?php

namespace Database\Seeders;

use App\Models\AnimalType;
use Illuminate\Database\Seeder;

/**
 * Reference data: starting animal categories. Safe to run repeatedly.
 * New categories can be added later from the app without changing this file.
 */
class AnimalTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Cattle',   'code_prefix' => 'CAT'],
            ['name' => 'Goats',    'code_prefix' => 'GOA'],
            ['name' => 'Sheep',    'code_prefix' => 'SHE'],
            ['name' => 'Chickens', 'code_prefix' => 'CHI'],
            ['name' => 'Pigs',     'code_prefix' => 'PIG'],
        ];

        foreach ($types as $type) {
            AnimalType::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($type['name'])],
                [
                    'name' => $type['name'],
                    'code_prefix' => $type['code_prefix'],
                    'is_active' => true,
                ]
            );
        }
    }
}
