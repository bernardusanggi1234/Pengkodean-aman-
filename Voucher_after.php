<?php

namespace App\Enums;

/**
 * Enum untuk status order — menggantikan Primitive Obsession pada field status.
 *
 * PHP akan melempar ValueError otomatis jika ada kode yang mencoba assign
 * string tidak valid, bahkan sebelum data menyentuh database.
 * Typo seperti "payed" tidak akan pernah lolos.
 */
enum OrderStatus: string
{
    case PENDING          = 'pending';
    case PAID             = 'paid';
    case SHIPPED          = 'shipped';
    case DELIVERED        = 'delivered';
    case REFUND_REQUESTED = 'refund_requested';
    case REFUNDED         = 'refunded';
    case CANCELLED        = 'cancelled';
}
