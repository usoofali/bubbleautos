<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case PENDING = 'pending';
    case PARTIALLY_PAID = 'partially_paid';
    case PAID = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending Payment',
            self::PARTIALLY_PAID => 'Partially Paid',
            self::PAID => 'Paid',
        };
    }

    public static function tryFromValue(?string $value): ?self
    {
        if (empty($value)) {
            return self::PENDING;
        }

        return match (strtolower($value)) {
            'unpaid', 'pending' => self::PENDING,
            'partial', 'partially_paid' => self::PARTIALLY_PAID,
            'paid' => self::PAID,
            default => self::tryFrom($value) ?? self::PENDING,
        };
    }
}
