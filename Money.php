<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\DB;

/**
 * Domain Service untuk operasi transfer antar wallet.
 *
 * Konsep: Domain Service
 * Transfer dipindah ke Domain Service karena melibatkan dua aggregate (dua Wallet).
 * Ini menghilangkan God Object dari controller.
 *
 * Transfer sepenuhnya atomic — jika credit() gagal, debit() ikut rollback.
 */
class WalletTransferService
{
    public function transfer(Wallet $from, Wallet $to, Money $amount): WalletTransaction
    {
        if ($from->id === $to->id) {
            throw new \DomainException('Cannot transfer to self');
        }

        return DB::transaction(function () use ($from, $to, $amount) {
            $from->debit($amount);
            $to->credit($amount);

            return WalletTransaction::create([
                'from_wallet_id' => $from->id,
                'to_wallet_id'   => $to->id,
                'amount'         => $amount->toCents(),
                'type'           => 'transfer',
                'status'         => 'completed',
            ]);
        });
    }
}
