<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'content',
        'language',
        'status',
        'template_id',
        'variables',
        'components'
    ];

    protected $casts = [
        'variables' => 'array',
        'components' => 'array'
    ];

    public function approvals()
    {
        return $this->hasMany(WhatsappTemplateApproval::class, 'template_id');
    }
}
