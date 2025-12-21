<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappPrice extends Model
{
    protected $fillable = [
        'menu_item_id',
        'sku',
        'name',
        'description',
        'benefits',
        'nutritional_info',
        'price',
        'promo_price',
        'icon',
        'is_active',
        'stock',
        'allow_quantity_selection',
        'min_quantity',
        'max_quantity'
    ];

    protected $casts = [
        'is_promo' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'float',
        'promo_price' => 'float',
        'metadata' => 'array',
        'characteristics' => 'array',
        'stock' => 'integer',
        'allow_quantity_selection' => 'boolean',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(WhatsappMenuItem::class, 'menu_item_id')
            ->withDefault([
                'id' => null,
                'title' => 'Sin categoría',
                'description' => null,
                'icon' => null
            ]);
    }

    public function hasStock(): bool
    {
        return $this->stock > 0;
    }

    public function canSelectQuantity(): bool
    {
        return $this->allow_quantity_selection;
    }

    public function isQuantityValid(int $quantity): bool
    {
        return $quantity >= $this->min_quantity && $quantity <= $this->max_quantity;
    }
}
