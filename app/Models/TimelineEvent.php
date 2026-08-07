<?php

namespace App\Models;

use App\Enums\TimelineEventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimelineEvent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'user_id',
        'event_type',
        'title',
        'description',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => TimelineEventType::class,
            'metadata' => 'json',
            'created_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
