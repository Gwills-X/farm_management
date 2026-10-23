<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\Sale;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleService
{
    /**
     * Records a sale and marks its animals as sold, atomically.
     *
     * Money is handled as integer kobo internally to avoid floating-point drift.
     *
     * @param  array{
     *     sale_date: string,
     *     buyer_name: string,
     *     buyer_phone?: ?string,
     *     payment_method?: ?string,
     *     amount_paid?: float|int|string|null,
     *     notes?: ?string,
     *     items: array<int, array{animal_id: int, unit_price: float|int|string}>
     * }  $data
     */
    public function record(array $data, ?int $userId = null): Sale
    {
        return DB::transaction(function () use ($data, $userId) {
            $items = $data['items'];
            $animalIds = collect($items)->pluck('animal_id')->map(fn ($id) => (int) $id);

            if ($animalIds->unique()->count() !== $animalIds->count()) {
                throw new DomainException('The same animal cannot appear twice in one sale.');
            }

            // Lock the animal rows so two concurrent sales cannot both take the same animal.
            $animals = Animal::query()
                ->whereIn('id', $animalIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($animals->count() !== $animalIds->count()) {
                throw new DomainException('One or more selected animals do not exist.');
            }

            $totalKobo = 0;
            foreach ($items as $item) {
                $animal = $animals[(int) $item['animal_id']];

                if (! $animal->isAvailableForSale()) {
                    throw new DomainException("Animal {$animal->animal_code} is not available for sale (status: {$animal->status}).");
                }

                $totalKobo += $this->toKobo($item['unit_price']);
            }

            $paidKobo = $this->toKobo($data['amount_paid'] ?? 0);

            if ($paidKobo > $totalKobo) {
                throw new DomainException('Amount paid cannot be greater than the sale total.');
            }

            $sale = Sale::create([
                'reference' => 'SAL-'.Str::upper(Str::random(8)),
                'sale_date' => $data['sale_date'],
                'buyer_name' => $data['buyer_name'],
                'buyer_phone' => $data['buyer_phone'] ?? null,
                'total_amount' => $totalKobo / 100,
                'amount_paid' => $paidKobo / 100,
                'payment_status' => $this->paymentStatus($paidKobo, $totalKobo),
                'payment_method' => $paidKobo > 0 ? ($data['payment_method'] ?? null) : null,
                'notes' => $data['notes'] ?? null,
                'recorded_by' => $userId,
            ]);

            foreach ($items as $item) {
                $sale->items()->create([
                    'animal_id' => (int) $item['animal_id'],
                    'unit_price' => $this->toKobo($item['unit_price']) / 100,
                ]);

                $animals[(int) $item['animal_id']]->update([
                    'status' => Animal::STATUS_SOLD,
                ]);
            }

            if ($paidKobo > 0) {
                $sale->payments()->create([
                    'amount' => $paidKobo / 100,
                    'payment_method' => $data['payment_method'] ?? null,
                    'paid_on' => $data['sale_date'],
                    'recorded_by' => $userId,
                ]);
            }

            return $sale->load('items');
        });
    }

    private function toKobo(float|int|string|null $amount): int
    {
        return (int) round(((float) ($amount ?? 0)) * 100);
    }

    private function paymentStatus(int $paidKobo, int $totalKobo): string
    {
        return match (true) {
            $paidKobo <= 0 => Sale::PAYMENT_UNPAID,
            $paidKobo >= $totalKobo => Sale::PAYMENT_PAID,
            default => Sale::PAYMENT_PARTIAL,
        };
    }
}
