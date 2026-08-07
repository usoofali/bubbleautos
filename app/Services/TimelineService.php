<?php

namespace App\Services;

use App\Enums\TimelineEventType;
use App\Models\Order;
use App\Models\TimelineEvent;
use Illuminate\Support\Facades\Auth;

class TimelineService
{
    public static function log(
        Order $order,
        TimelineEventType $eventType,
        string $title,
        ?string $description = null,
        ?array $metadata = null,
        ?int $userId = null
    ): TimelineEvent {
        return TimelineEvent::create([
            'order_id' => $order->id,
            'user_id' => $userId ?? Auth::id(),
            'event_type' => $eventType,
            'title' => $title,
            'description' => $description,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
