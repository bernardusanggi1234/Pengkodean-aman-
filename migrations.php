<?php

namespace App\ValueObjects;

/**
 * Value Object untuk kode voucher.
 *
 * Konsep: Value Object — menghilangkan Primitive Obsession pada kode voucher.
 * Format divalidasi di constructor sehingga kode yang salah format
 * tidak pernah bisa masuk ke sistem.
 * Normalisasi (uppercase, trim) otomatis dilakukan di fromString().
 */
final class VoucherCode
{
    private function __construct(private readonly string $code)
    {
        if (!preg_match('/^[A-Z0-9]{4,16}$/', $code)) {
            throw new \InvalidArgumentException(
                "Invalid voucher code format: '{$code}'."
            );
        }
    }

    /** Normalisasi otomatis: uppercase dan trim */
    public static function fromString(string $code): self
    {
        return new self(strtoupper(trim($code)));
    }

    public function toString(): string
    {
        return $this->code;
    }

    public function equals(VoucherCode $other): bool
    {
        return $this->code === $other->code;
    }

    public function __toString(): string
    {
        return $this->code;
    }
}
