<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * KODE BERMASALAH — Anemic Domain Model + Boolean Flag Hell
 *
 * Masalah:
 * 1. Status string bebas → typo "payed" bisa masuk database
 * 2. Boolean flag hell → is_refunded=true tapi is_paid=false bisa terjadi
 * 3. Tidak ada method apapun — pure anemic domain model
 */
class Order extends Model
{
    // MASALAH: Status string bebas, typo tidak terdeteksi
    protected $fillable = ['user_id', 'amount', 'status'];

    // MASALAH: Boolean flag hell — 5 boolean bisa kombinasi tidak valid
    protected $casts = [
        'is_paid'            => 'boolean',
        'is_shipped'         => 'boolean',
        'is_delivered'       => 'boolean',
        'is_refunded'        => 'boolean',
        'is_refund_approved' => 'boolean',
    ];

    // Tidak ada method apapun — zero business logic
}
