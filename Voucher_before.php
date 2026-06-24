<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Immutable Audit Log.
 *
 * Konsep: Immutability
 * Audit log bersifat immutable — sekali dicatat tidak bisa diubah atau dihapus.
 * Listener LogOrderEvent akan membuat record baru setiap kali
 * event OrderPaid atau OrderRefunded dipanggil.
 */
class AuditLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['event', 'model_type', 'model_id', 'data', 'user_id'];

    protected $casts = ['data' => 'array'];

    protected static function boot()
    {
        parent::boot();

        static::updating(fn () => throw new \Exception('AuditLog is immutable.'));
        static::deleting(fn () => throw new \Exception('AuditLog is immutable.'));
    }
}
