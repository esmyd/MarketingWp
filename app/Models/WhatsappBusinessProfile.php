<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappBusinessProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'whatsapp_business_id',
        'phone_number',
        'phone_number_id',
        'business_name',
        'display_name',
        'status',
        'access_token',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function messages()
    {
        return $this->hasMany(WhatsappMessage::class);
    }

    public function conversations()
    {
        return $this->hasMany(WhatsappConversation::class);
    }

    public function contacts()
    {
        return $this->hasMany(WhatsappContact::class);
    }
}
