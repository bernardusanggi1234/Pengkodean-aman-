<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * KODE BERMASALAH — God Object + No Validation + No Locking
 *
 * Masalah:
 * 1. Tidak ada validasi saldo — bisa negatif
 * 2. Transfer tidak atomic — uang bisa hilang jika gagal di tengah
 * 3. God Object — logic tidak relevan ada di model wallet
 * 4. Tidak ada locking — race condition possible
 */
class Wallet extends Model
{
    protected $fillable = ['user_id', 'balance'];

    // MASALAH: Tidak ada validasi, saldo langsung dikurangi (bisa negatif)
    public function withdraw($amount)
    {
        $this->balance -= $amount; // Bisa negatif!
        $this->save();
    }

    // MASALAH: Transfer tidak atomic, tidak ada locking
    public function transfer($toWallet, $amount)
    {
        $this->balance -= $amount;
        $toWallet->balance += $amount;
        $this->save();
        $toWallet->save(); // Jika gagal di sini, uang hilang dari source
    }

    // MASALAH: God Object — logic tidak relevan ada di model wallet
    public function sendNotification($message)
    {
        // Seharusnya di NotificationService
    }

    public function generateReport()
    {
        // Seharusnya di ReportService
    }

    public function detectFraud()
    {
        // Seharusnya di Event Listener
    }
}
