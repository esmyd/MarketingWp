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
        'chatgpt_enabled' => 'boolean',
        'greetings' => 'array',
        'menu_commands' => 'array',
        'metadata' => 'array',
        'chatgpt_additional_params' => 'array',
        'chatgpt_max_tokens' => 'integer',
        'chatgpt_temperature' => 'float'
    ];

    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(WhatsappBusinessProfile::class, 'business_profile_id');
    }
}
