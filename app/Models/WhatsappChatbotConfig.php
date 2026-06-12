<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappChatbotConfig extends Model
{
    protected $fillable = [
        'business_profile_id',
        'welcome_message',
        'default_response',
        'greetings',
        'menu_commands',
        'metadata',
        'is_active',
        'monitoring_enabled',
        'monitoring_phone_number',
        'monitoring_email',
        'chatgpt_enabled',
        'chatgpt_api_key',
        'chatgpt_model',
        'chatgpt_system_prompt',
        'chatgpt_max_tokens',
        'chatgpt_temperature',
        'chatgpt_additional_params'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'monitoring_enabled' => 'boolean',
        'chatgpt_enabled' => 'boolean',
        'greetings' => 'array',
        'menu_commands' => 'array',
        'metadata' => 'array',
        'chatgpt_additional_params' => 'array',
        'chatgpt_max_tokens' => 'integer',
        'chatgpt_temperature' => 'float'
    ];

    public function getBotNameAttribute(): ?string
    {
        return $this->metadata['bot_name'] ?? null;
    }

    public function getFallbackMessageAttribute(): ?string
    {
        return $this->default_response;
    }

    public function getResponseDelayAttribute(): int
    {
        return (int) ($this->metadata['response_delay'] ?? 1000);
    }

    public function getPrimaryColorAttribute(): string
    {
        return $this->metadata['primary_color'] ?? '#3B82F6';
    }

    public function getSecondaryColorAttribute(): string
    {
        return $this->metadata['secondary_color'] ?? '#1E40AF';
    }

    public function getBotAvatarAttribute(): ?string
    {
        return $this->metadata['bot_avatar'] ?? null;
    }

    public function getBotAvatarUrlAttribute(): ?string
    {
        $path = $this->metadata['bot_avatar_path'] ?? null;
        if ($path) {
            return asset('storage/' . ltrim($path, '/'));
        }

        $url = $this->metadata['bot_avatar'] ?? null;

        return $url !== null && $url !== '' ? $url : null;
    }

    public function getFontFamilyAttribute(): string
    {
        return $this->metadata['font_family'] ?? 'Arial';
    }

    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(WhatsappBusinessProfile::class, 'business_profile_id');
    }
}
