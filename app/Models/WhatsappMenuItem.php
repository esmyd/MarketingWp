<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappMenuItem extends Model
{
    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'description',
        'action_id',
        'icon',
        'order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(WhatsappMenu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(WhatsappMenuItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(WhatsappMenuItem::class, 'parent_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(WhatsappPrice::class, 'menu_item_id');
    }
}
