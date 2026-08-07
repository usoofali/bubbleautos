<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case PENDING = 'pending';
    case IN_TRANSIT = 'in_transit';
    case DELIVERED = 'delivered';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::IN_TRANSIT => 'In Transit',
            self::DELIVERED => 'Delivered',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'slate',
            self::IN_TRANSIT => 'blue',
            self::DELIVERED => 'emerald',
        };
    }
}
