<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

/**
 * KODE BERMASALAH
 *
 * Masalah:
 * 1. ship() bisa dipanggil tanpa cek apakah sudah paid
 * 2. approveRefund() bisa dipanggil meski refund_requested_at masih null
 * 3. updateAmount() bisa mengubah amount kapan saja, bahkan setelah paid
 */
class OrderController extends Controller
{
    public function ship($id)
    {
        $order = Order::find($id);

        // MASALAH: Bisa ship tanpa cek apakah sudah paid
        $order->status     = 'shipped';
        $order->is_shipped = true;
        $order->shipped_at = now();
        $order->save();

        return response()->json($order);
    }

    public function approveRefund($id)
    {
        $order = Order::find($id);

        // MASALAH: Bisa approve meski refund_requested_at masih null
        $order->status             = 'refunded';
        $order->is_refunded        = true;
        $order->is_refund_approved = true;
        $order->refunded_at        = now();
        $order->save();

        return response()->json($order);
    }

    public function updateAmount(Request $request, $id)
    {
        $order = Order::find($id);

        // MASALAH: Amount bisa diubah kapan saja, bahkan setelah paid
        $order->amount = $request->amount;
        $order->save();

        return response()->json($order);
    }
}
