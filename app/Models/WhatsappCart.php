<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappCart extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PAYMENT_PENDING = 'payment_pending';
    const STATUS_PAID = 'paid';

    protected $fillable = [
        'contact_id',
        'total',
        'status',
        'metadata',
        'note',
        'payment_status',
        'payment_method',
        'payment_reference'
    ];

    protected $casts = [
        'metadata' => 'array',
        'total' => 'decimal:2'
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(WhatsappContact::class);
    }

    public function items()
    {
        return $this->hasMany(WhatsappCartItem::class);
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isConfirmed()
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isPaymentPending()
    {
        return $this->status === self::STATUS_PAYMENT_PENDING;
    }

    public function isPaid()
    {
        return $this->status === self::STATUS_PAID;
    }

    public function confirm()
    {
        $this->status = self::STATUS_CONFIRMED;
        $this->save();
    }

    public function markAsPaymentPending($paymentMethod = null)
    {
        $this->status = self::STATUS_PAYMENT_PENDING;
        $this->payment_method = $paymentMethod;
        $this->save();
    }

    public function markAsPaid($paymentReference = null)
    {
        $this->status = self::STATUS_PAID;
        $this->payment_reference = $paymentReference;
        $this->save();
    }

    public function cancel()
    {
        $this->status = self::STATUS_CANCELLED;
        $this->save();
    }
}
