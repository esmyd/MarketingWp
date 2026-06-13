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
        return self::normalizeHexColor($this->metadata['primary_color'] ?? null, '#005c4b');
    }

    public function getSecondaryColorAttribute(): string
    {
        return self::normalizeHexColor($this->metadata['secondary_color'] ?? null, '#075e54');
    }

    /** @return array{r: int, g: int, b: int} */
    public function primaryColorRgb(): array
    {
        return self::hexToRgb($this->primary_color);
    }

    public static function normalizeHexColor(?string $value, string $default): string
    {
        if ($value === null || trim($value) === '') {
            return $default;
        }

        $value = trim($value);

        if (preg_match('/^#([0-9a-fA-F]{6})$/', $value, $m)) {
            return '#' . strtolower($m[1]);
        }

        if (preg_match('/^#([0-9a-fA-F]{3})$/', $value, $m)) {
            $c = $m[1];

            return '#' . strtolower($c[0] . $c[0] . $c[1] . $c[1] . $c[2] . $c[2]);
        }

        if (preg_match('/^([0-9a-fA-F]{6})$/', $value, $m)) {
            return '#' . strtolower($m[1]);
        }

        return $default;
    }

    /** @return array{r: int, g: int, b: int} */
    public static function hexToRgb(string $hex): array
    {
        $hex = ltrim(self::normalizeHexColor($hex, '#000000'), '#');

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
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
