<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Events\OrderPaid;
use App\Events\OrderRefunded;
use App\Exceptions\ImmutableFieldException;
use App\Exceptions\InvalidStateTransition;
use Illuminate\Database\Eloquent\Model;

/**
 * KODE DIPERBAIKI — State Machine + Immutability + Deep Model
 *
 * Perbaikan:
 * 1. State Machine — transisi status dikontrol ketat, urutan tidak bisa dilangkahi
 * 2. Immutability — amount tidak bisa diubah setelah order tersimpan
 * 3. Enum OrderStatus — typo langsung error, boolean flag dihapus total
 *
 * Alur valid: PENDING → PAID → SHIPPED → DELIVERED → REFUND_REQUESTED → REFUNDED
 */
class Order extends Model
{
    protected $guarded = ['id', 'amount', 'user_id', 'order_date'];

    protected $casts = [
        'status' => OrderStatus::class, // Enum — typo langsung error
        // Semua boolean flag dihapus
    ];

    /**
     * Immutability: amount tidak bisa diubah setelah record ada di DB.
     */
    public function setAmountAttribute(mixed $value): void
    {
        if ($this->exists) {
            throw new ImmutableFieldException('amount');
        }

        $this->attributes['amount'] = $value;
    }

    /**
     * State machine: PENDING → PAID
     */
    public function confirmPayment(): void
    {
        if ($this->status !== OrderStatus::PENDING) {
            throw new InvalidStateTransition($this->status->value, 'paid');
        }

        $this->status  = OrderStatus::PAID;
        $this->paid_at = now();
        $this->save();

        event(new OrderPaid($this));
    }

    /**
     * State machine: PAID → SHIPPED (tidak bisa skip dari PENDING)
     */
    public function ship(): void
    {
        if ($this->status !== OrderStatus::PAID) {
            throw new InvalidStateTransition($this->status->value, 'shipped');
        }

        $this->status     = OrderStatus::SHIPPED;
        $this->shipped_at = now();
        $this->save();
    }

    /**
     * State machine: SHIPPED → DELIVERED
     */
    public function confirmDelivery(): void
    {
        if ($this->status !== OrderStatus::SHIPPED) {
            throw new InvalidStateTransition($this->status->value, 'delivered');
        }

        $this->status       = OrderStatus::DELIVERED;
        $this->delivered_at = now();
        $this->save();
    }

    /**
     * State machine: DELIVERED → REFUND_REQUESTED
     */
    public function requestRefund(string $reason): void
    {
        if ($this->status !== OrderStatus::DELIVERED) {
            throw new InvalidStateTransition($this->status->value, 'refund_requested');
        }

        $this->status              = OrderStatus::REFUND_REQUESTED;
        $this->refund_reason       = $reason;
        $this->refund_requested_at = now();
        $this->save();
    }

    /**
     * State machine: REFUND_REQUESTED → REFUNDED
     * Tidak bisa approve refund jika belum ada request.
     */
    public function approveRefund(): void
    {
        if ($this->status !== OrderStatus::REFUND_REQUESTED) {
            throw new InvalidStateTransition($this->status->value, 'refunded');
        }

        $this->status      = OrderStatus::REFUNDED;
        $this->refunded_at = now();
        $this->save();

        event(new OrderRefunded($this));
    }
}
