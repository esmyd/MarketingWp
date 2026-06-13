<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_profile_id',
        'phone_number',
        'name',
        'status',
        'bot_enabled',
        'last_inbound_message_id',
        'last_inbound_at',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'bot_enabled' => 'boolean',
        'last_inbound_at' => 'datetime',
    ];

    public function businessProfile()
    {
        return $this->belongsTo(WhatsappBusinessProfile::class);
    }

    public function messages()
    {
        return $this->hasMany(WhatsappMessage::class, 'contact_id');
    }

    public function latestMessage()
    {
        return $this->hasOne(WhatsappMessage::class, 'contact_id')->latestOfMany('created_at');
    }

    public function conversations()
    {
        return $this->hasMany(WhatsappConversation::class);
    }

    public function carts()
    {
        return $this->hasMany(WhatsappCart::class);
    }

    public function needsAgent(): bool
    {
        return !empty($this->metadata['needs_agent']);
    }

    public function requestAgentHandoff(string $source = 'unknown'): void
    {
        $metadata = $this->metadata ?? [];
        $metadata['needs_agent'] = true;
        $metadata['agent_requested_at'] = now()->toIso8601String();
        $metadata['agent_request_source'] = $source;
        $this->metadata = $metadata;
        $this->save();
    }

    public function clearAgentRequest(): void
    {
        if (!$this->needsAgent()) {
            return;
        }

        $metadata = $this->metadata ?? [];
        unset($metadata['needs_agent'], $metadata['agent_requested_at'], $metadata['agent_request_source']);
        $this->metadata = $metadata;
        $this->save();
    }
}
