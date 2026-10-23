<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SalePayment;
use DomainException;
use Illuminate\Support\Facades\DB;

class SalePaymentService
{
    /**
     * Records a payment against an existing sale. The sale row is locked so two
     * payments submitted at the same time cannot overpay the balance.
     */
    public function record(Sale $sale, float|string $amount, ?string $method, string $paidOn, ?int $userId = null): SalePayment
    {
        return DB::transaction(function () use ($sale, $amount, $method, $paidOn, $userId) {
            $locked = Sale::query()->whereKey($sale->getKey())->lockForUpdate()->firstOrFail();

            $totalKobo = (int) round((float) $locked->total_amount * 100);
            $paidKobo = (int) round((float) $locked->amount_paid * 100);
            $outstandingKobo = $totalKobo - $paidKobo;
            $amountKobo = (int) round((float) $amount * 100);

            if ($outstandingKobo <= 0) {
                throw new DomainException('This sale is already fully paid.');
            }

            if ($amountKobo <= 0 || $amountKobo > $outstandingKobo) {
                throw new DomainException(sprintf(
                    'Payment must be greater than zero and no more than the outstanding balance of %s.',
                    number_format($outstandingKobo / 100, 2)
                ));
            }

            $newPaidKobo = $paidKobo + $amountKobo;

            $locked->update([
                'amount_paid' => $newPaidKobo / 100,
                'payment_status' => $newPaidKobo >= $totalKobo ? Sale::PAYMENT_PAID : Sale::PAYMENT_PARTIAL,
                'payment_method' => $method ?? $locked->payment_method,
            ]);

            return $locked->payments()->create([
                'amount' => $amountKobo / 100,
                'payment_method' => $method,
                'paid_on' => $paidOn,
                'recorded_by' => $userId,
            ]);
        });
    }
}
