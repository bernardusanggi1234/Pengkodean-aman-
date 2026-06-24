<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidStateTransition;
use App\Models\Order;
use Illuminate\Http\Request;

/**
 * KODE DIPERBAIKI — Thin Controller
 *
 * Controller sekarang tipis — hanya memanggil transition method di model
 * dan menangkap exception menjadi HTTP response.
 *
 * updateAmount() DIHAPUS TOTAL — amount adalah immutable field.
 */
class OrderController extends Controller
{
    public function ship($id)
    {
        $order = Order::findOrFail($id);

        try {
            $order->ship(); // Validasi urutan ada di model
        } catch (InvalidStateTransition $e) {
            return response()->json(['message' => 'Cannot ship order that is not paid'], 422);
        }

        return response()->json($order);
    }

    public function approveRefund($id)
    {
        $order = Order::findOrFail($id);

        try {
            $order->approveRefund();
        } catch (InvalidStateTransition $e) {
            return response()->json(['message' => 'No refund request found'], 422);
        }

        return response()->json($order);
    }

    // updateAmount() DIHAPUS TOTAL
    // Tidak boleh ada endpoint untuk mengubah amount setelah order dibuat
}
