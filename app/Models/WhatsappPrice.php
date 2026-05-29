<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappPrice extends Model
{
    protected $fillable = [
        'menu_item_id',
        'category',
        'sku',
        'name',
        'description',
        'benefits',
        'characteristics',
        'price',
        'promo_price',
        'currency',
        'is_promo',
        'promo_start_date',
        'promo_end_date',
        'is_active',
        'stock',
        'allow_quantity_selection',
        'min_quantity',
        'max_quantity',
        'image',
        'metadata',
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
        'max_quantity' => 'integer',
        'promo_start_date' => 'date',
        'promo_end_date' => 'date',
    ];

    protected $appends = ['icon'];

    public function getIconAttribute(): string
    {
        if ($this->relationLoaded('menuCategory') && $this->menuCategory?->icon) {
            return $this->menuCategory->icon;
        }

        if ($this->menu_item_id) {
            $icon = WhatsappMenuItem::whereKey($this->menu_item_id)->value('icon');

            return $icon ?: '📦';
        }

        return '📦';
    }

    public function menuCategory(): BelongsTo
    {
        return $this->belongsTo(WhatsappMenuItem::class, 'menu_item_id')
            ->withDefault([
                'id' => null,
                'title' => 'Sin categoría',
                'description' => null,
                'icon' => null
            ]);
    }

    /** @deprecated Use menuCategory() — alias kept for compatibility */
    public function category(): BelongsTo
    {
        return $this->menuCategory();
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
