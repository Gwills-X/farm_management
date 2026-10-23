<?php

namespace App\Services;

use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseService
{
    public function __construct(private AnimalRegistrationService $registration)
    {
    }

    /**
     * Records a purchase and registers each purchased animal. The purchase, the
     * animals, and the cost links are created in one transaction, so a failure
     * leaves no partial purchase behind.
     *
     * @param  array{purchase_date: string, seller_name?: ?string, notes?: ?string,
     *     items: array<int, array<string, mixed>>}  $data
     */
    public function record(array $data, ?int $userId = null): Purchase
    {
        return DB::transaction(function () use ($data, $userId) {
            $totalKobo = 0;
            foreach ($data['items'] as $item) {
                $totalKobo += (int) round((float) $item['unit_cost'] * 100);
            }

            $purchase = Purchase::create([
                'reference' => 'PUR-'.Str::upper(Str::random(8)),
                'purchase_date' => $data['purchase_date'],
                'seller_name' => $data['seller_name'] ?? null,
                'total_cost' => $totalKobo / 100,
                'notes' => $data['notes'] ?? null,
                'recorded_by' => $userId,
            ]);

            foreach ($data['items'] as $item) {
                $animal = $this->registration->register([
                    'animal_type_id' => $item['animal_type_id'],
                    'name' => $item['name'] ?? null,
                    'sex' => $item['sex'] ?? 'unknown',
                    'breed' => $item['breed'] ?? null,
                    'date_of_birth' => $item['date_of_birth'] ?? null,
                    'acquisition_type' => 'purchased',
                    'acquisition_date' => $data['purchase_date'],
                    'source' => $data['seller_name'] ?? null,
                    'weight_kg' => $item['weight_kg'] ?? null,
                ], $userId);

                $purchase->items()->create([
                    'animal_id' => $animal->id,
                    'unit_cost' => (int) round((float) $item['unit_cost'] * 100) / 100,
                ]);
            }

            return $purchase->load('items.animal');
        });
    }
}
