<?php

namespace App\ValueObjects;

/**
 * Value Object untuk merepresentasikan uang.
 *
 * Keunggulan:
 * - Amount tidak pernah negatif (dijaga di constructor)
 * - Disimpan dalam cents (integer) untuk menghindari floating point error
 * - Class final dan constructor private — tidak bisa di-bypass
 *
 * Digunakan di: Order, Wallet, Discount
 */
final class Money
{
    private function __construct(private readonly int $cents)
    {
        if ($cents < 0) {
            throw new \InvalidArgumentException('Amount cannot be negative.');
        }
    }

    public static function fromRupiah(float $rupiah): self
    {
        return new self((int) round($rupiah * 100));
    }

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public function toCents(): int
    {
        return $this->cents;
    }

    public function toRupiah(): float
    {
        return $this->cents / 100;
    }

    public function isGreaterThan(Money $other): bool
    {
        return $this->cents > $other->cents;
    }

    public function equals(Money $other): bool
    {
        return $this->cents === $other->cents;
    }
}
