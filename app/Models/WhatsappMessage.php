<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'business_profile_id',
        'message_id',
        'sender_type',
        'receiver_type',
        'content',
        'type',
        'status',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'business_profile_id' => 'integer'
    ];

    public function contact()
    {
        return $this->belongsTo(WhatsappContact::class);
    }

    public function businessProfile()
    {
        return $this->belongsTo(WhatsappBusinessProfile::class);
    }

    public function conversation()
    {
        return $this->belongsTo(WhatsappConversation::class);
    }
}
