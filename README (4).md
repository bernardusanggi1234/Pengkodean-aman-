<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Voucher;
use Illuminate\Http\Request;

/**
 * KODE BERMASALAH
 *
 * Masalah:
 * 1. Tidak ada idempotency key — retry = double redeem
 * 2. Race condition — tidak ada locking
 * 3. Tidak ada validasi: expired, min purchase, user eligibility
 * 4. checkUsage() mengekspos usage_count dan max_usage → enumeration attack
 */
class VoucherController extends Controller
{
    public function redeem(Request $request)
    {
        $voucher = Voucher::where('code', $request->code)->first();
        $order   = Order::find($request->order_id);

        // MASALAH: Tidak ada idempotency key, retry = double redeem
        // MASALAH: Race condition — tidak ada locking
        if ($voucher->usage_count < $voucher->max_usage) {
            $voucher->usage_count++;
            $voucher->save();

            // MASALAH: Tidak ada validasi expired, min purchase, user eligibility
            $discount        = $voucher->discount_value;
            $order->discount = $discount;
            $order->save();

            return response()->json([
                'message'  => 'Voucher applied',
                'discount' => $discount,
            ]);
        }

        return response()->json(['message' => 'Voucher not available'], 400);
    }

    // MASALAH: Mengekspos usage_count dan max_usage → enumeration attack
    public function checkUsage($code)
    {
        $voucher = Voucher::where('code', $code)->first();

        return response()->json([
            'code'        => $voucher->code,
            'usage_count' => $voucher->usage_count,
            'max_usage'   => $voucher->max_usage,
            'remaining'   => $voucher->max_usage - $voucher->usage_count,
        ]);
    }
}
