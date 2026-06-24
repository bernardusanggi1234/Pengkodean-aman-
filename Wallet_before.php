<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\WalletTransferService;
use App\ValueObjects\Money;
use Illuminate\Http\Request;

/**
 * KODE DIPERBAIKI — Thin Controller
 *
 * Controller sekarang benar-benar tipis:
 * - Hanya validasi input HTTP
 * - Handle exception dari domain layer
 * - Semua domain logic ada di Wallet model dan WalletTransferService
 *
 * sendNotification(), generateReport(), detectFraud() DIHAPUS dari controller.
 */
class WalletController extends Controller
{
    public function __construct(private WalletTransferService $transferService) {}

    public function withdraw(Request $request)
    {
        $validated = $request->validate([
            'wallet_id' => 'required|exists:wallets,id',
            'amount'    => 'required|numeric|min:0.01',
        ]);

        $wallet = Wallet::findOrFail($validated['wallet_id']);

        try {
            $wallet->debit(Money::fromRupiah($validated['amount']));

            WalletTransaction::create([
                'from_wallet_id' => $wallet->id,
                'amount'         => Money::fromRupiah($validated['amount'])->toCents(),
                'type'           => 'withdrawal',
                'status'         => 'completed',
            ]);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($wallet->fresh());
    }

    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'from_wallet_id' => 'required|exists:wallets,id',
            'to_wallet_id'   => 'required|exists:wallets,id',
            'amount'         => 'required|numeric|min:0.01',
        ]);

        $fromWallet = Wallet::findOrFail($validated['from_wallet_id']);
        $toWallet   = Wallet::findOrFail($validated['to_wallet_id']);

        try {
            $this->transferService->transfer(
                $fromWallet,
                $toWallet,
                Money::fromRupiah($validated['amount'])
            );
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'from' => $fromWallet->fresh(),
            'to'   => $toWallet->fresh(),
        ]);
    }
}
