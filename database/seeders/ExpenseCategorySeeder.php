<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

/**
 * Reference data: starting expense categories. Safe to run repeatedly.
 */
class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Animal Feed',
            'Veterinary Services',
            'Medication',
            'Labour',
            'Transport',
            'Equipment',
            'Other',
        ];

        foreach ($names as $name) {
            ExpenseCategory::firstOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }
    }
}
