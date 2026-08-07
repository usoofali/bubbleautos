<?php

namespace App\Models;

use App\Enums\EmailStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Email extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'message_id',
        'sender',
        'recipient',
        'subject',
        'body',
        'attachments_count',
        'processing_status',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'attachments_count' => 'integer',
            'processing_status' => EmailStatus::class,
            'received_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(EmailAttachment::class);
    }
}
