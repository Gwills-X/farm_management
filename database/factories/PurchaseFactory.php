<?php

namespace Database\Factories;

use App\Models\Purchase;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Purchase> */
class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition(): array
    {
        return [
            'reference' => 'PUR-'.Str::upper(Str::random(8)),
            'purchase_date' => now()->toDateString(),
            'seller_name' => fake()->name(),
            'total_cost' => 0,
            'recorded_by' => null,
        ];
    }
}
