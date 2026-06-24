<?php

namespace App\Models;

use App\Events\WalletCredited;
use App\Events\WalletDebited;
use App\Events\WalletSuspended;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * KODE DIPERBAIKI — Pessimistic Locking + Daily Limit + Atomic Transaction
 *
 * Perbaikan:
 * 1. Pessimistic Locking via lockForUpdate() — row wallet di-lock di level
 *    database sehingga dua request bersamaan tidak bisa sama-sama lolos validasi
 * 2. Daily limit — tracked dengan field daily_spent yang di-reset setiap hari
 * 3. debit() dan credit() dibungkus DB::transaction() → atomic
 * 4. God Object methods dihapus — dipindah ke service/listener terpisah
 */
class Wallet extends Model
{
    protected $fillable = ['user_id', 'balance', 'daily_spent', 'daily_spent_date', 'is_suspended'];

    private const DAILY_LIMIT_RUPIAH = 10_000_000;

    protected $casts = [
        'is_suspended'    => 'boolean',
        'daily_spent_date' => 'date',
    ];

    private function canDebit(Money $amount): bool
    {
        $balanceInCents = (int) round($this->balance * 100);

        return $balanceInCents >= $amount->toCents();
    }

    private function exceedsDailyLimit(Money $amount): bool
    {
        // Reset jika sudah beda hari
        if ($this->daily_spent_date?->isToday() === false) {
            $this->daily_spent      = 0;
            $this->daily_spent_date = now()->toDateString();
        }

        $dailySpentCents = (int) round($this->daily_spent * 100);
        $dailyLimitCents = self::DAILY_LIMIT_RUPIAH * 100;

        return ($dailySpentCents + $amount->toCents()) > $dailyLimitCents;
    }

    public function debit(Money $amount): void
    {
        DB::transaction(function () use ($amount) {
            // Pessimistic lock — cegah race condition
            $wallet = Wallet::lockForUpdate()->find($this->id);

            if ($wallet->is_suspended) {
                throw new \DomainException('Wallet is suspended.');
            }

            if (!$wallet->canDebit($amount)) {
                throw new \DomainException('Insufficient balance');
            }

            if ($wallet->exceedsDailyLimit($amount)) {
                throw new \DomainException('Daily limit exceeded');
            }

            $wallet->balance          -= $amount->toRupiah();
            $wallet->daily_spent      += $amount->toRupiah();
            $wallet->daily_spent_date  = now()->toDateString();
            $wallet->save();

            $this->refresh();

            event(new WalletDebited($wallet, $amount));
        });
    }

    public function credit(Money $amount): void
    {
        DB::transaction(function () use ($amount) {
            $wallet = Wallet::lockForUpdate()->find($this->id);

            $wallet->balance += $amount->toRupiah();
            $wallet->save();

            $this->refresh();

            event(new WalletCredited($wallet, $amount));
        });
    }

    public function suspend(string $reason): void
    {
        $this->is_suspended     = true;
        $this->suspended_reason = $reason;
        $this->suspended_at     = now();
        $this->save();

        event(new WalletSuspended($this, $reason));
    }
}
