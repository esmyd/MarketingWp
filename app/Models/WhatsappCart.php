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
        'payment_reference',
        'requires_invoice',
        'invoice_status',
        'invoice_data',
    ];

    protected $casts = [
        'metadata' => 'array',
        'invoice_data' => 'array',
        'total' => 'decimal:2',
        'requires_invoice' => 'boolean',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(WhatsappContact::class);
    }

    public function items()
    {
        return $this->hasMany(WhatsappCartItem::class, 'whatsapp_cart_id');
    }

    public function notes()
    {
        return $this->hasMany(WhatsappCartNote::class, 'whatsapp_cart_id');
    }

    /**
     * Carritos en compra (active) no son pedidos cerrados.
     */
    public function scopeReportable($query)
    {
        return $query->whereNotIn('status', ['active', 'abandoned']);
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

    public function hasPaymentProof(): bool
    {
        return !empty($this->metadata['payment_proof']);
    }

    public function isAwaitingPaymentProof(): bool
    {
        return $this->payment_status === 'awaiting_proof'
            || !empty($this->metadata['pending_payment_proof']);
    }

    public function attachPaymentProof(array $proofData): void
    {
        $metadata = $this->metadata ?? [];
        $metadata['payment_proof'] = $proofData;
        unset($metadata['pending_payment_proof']);
        $this->metadata = $metadata;
        $this->payment_status = 'proof_submitted';
        $this->save();
    }

    public function markAwaitingPaymentProof(): void
    {
        $metadata = $this->metadata ?? [];
        $metadata['pending_payment_proof'] = true;
        $this->metadata = $metadata;
        $this->payment_status = 'awaiting_proof';
        $this->save();
    }

    public function getOrderNumber(): string
    {
        return $this->metadata['order_details']['order_number']
            ?? 'ORD-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
