<?php

namespace App\Listeners;

use App\Events\WalletDebited;
use App\Models\WalletTransaction;

/**
 * Event Listener untuk deteksi anomali transaksi.
 *
 * Konsep: Event-Driven Architecture
 * Anomaly detection diimplementasikan sebagai Event Listener terpisah,
 * bukan di dalam model atau controller.
 *
 * Setiap kali WalletDebited event dipanggil, listener ini mengecek
 * frekuensi transaksi. Jika lebih dari 10 transaksi dalam 5 menit,
 * wallet akan otomatis di-suspend.
 *
 * Komponen tidak saling bergantung langsung — Wallet tidak perlu tahu
 * soal fraud detection, fraud detection tidak perlu tahu soal internal Wallet.
 */
class DetectAnomalousActivity
{
    public function handle(WalletDebited $event): void
    {
        $wallet = $event->wallet;

        $recentCount = WalletTransaction::where('from_wallet_id', $wallet->id)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        // Lebih dari 10 transaksi dalam 5 menit → suspend otomatis
        if ($recentCount > 10) {
            $wallet->suspend('Anomaly detected: high transaction frequency');
        }
    }
}
