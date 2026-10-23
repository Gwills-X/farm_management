<?php

namespace Database\Factories;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Sale> */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        return [
            'reference' => 'SAL-'.Str::upper(Str::random(8)),
            'sale_date' => now()->toDateString(),
            'buyer_name' => fake()->name(),
            'total_amount' => 0,
            'amount_paid' => 0,
            'payment_status' => Sale::PAYMENT_UNPAID,
            'payment_method' => null,
            'recorded_by' => null,
        ];
    }
}
