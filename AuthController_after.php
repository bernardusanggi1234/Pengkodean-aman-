<?php

namespace App\ValueObjects;

enum DiscountType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED      = 'fixed';
}

/**
 * Value Object untuk diskon voucher.
 *
 * Konsep: Value Object
 * Mengenkapsulasi logika kalkulasi diskon termasuk max cap.
 * - Diskon tidak bisa negatif
 * - Tidak bisa melebihi harga asli
 * - Max cap di-enforce secara otomatis
 */
final class Discount
{
    private function __construct(
        private readonly int          $amountCents,
        private readonly DiscountType $type
    ) {
        if ($amountCents < 0) {
            throw new \InvalidArgumentException('Discount amount cannot be negative.');
        }
    }

    public static function percentage(int $percentage): self
    {
        if ($percentage < 1 || $percentage > 100) {
            throw new \InvalidArgumentException('Percentage must be between 1 and 100.');
        }

        return new self($percentage, DiscountType::PERCENTAGE);
    }

    public static function fixed(Money $amount): self
    {
        return new self($amount->toCents(), DiscountType::FIXED);
    }

    /**
     * Hitung diskon dengan dukungan max cap.
     */
    public function apply(Money $originalPrice, ?Money $maxCap = null): Money
    {
        if ($this->type === DiscountType::PERCENTAGE) {
            $discountCents = (int) round($originalPrice->toCents() * ($this->amountCents / 100));
        } else {
            $discountCents = $this->amountCents;
        }

        if ($maxCap !== null && $discountCents > $maxCap->toCents()) {
            $discountCents = $maxCap->toCents();
        }

        // Diskon tidak boleh melebihi harga asli
        if ($discountCents > $originalPrice->toCents()) {
            $discountCents = $originalPrice->toCents();
        }

        return Money::fromCents($discountCents);
    }
}
