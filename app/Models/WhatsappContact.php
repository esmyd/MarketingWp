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
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'bot_enabled' => 'boolean'
    ];

    public function businessProfile()
    {
        return $this->belongsTo(WhatsappBusinessProfile::class);
    }

    public function messages()
    {
        return $this->hasMany(WhatsappMessage::class);
    }

    public function conversations()
    {
        return $this->hasMany(WhatsappConversation::class);
    }

    public function carts()
    {
        return $this->hasMany(WhatsappCart::class);
    }
}
