<?php

namespace App\Enums;

enum EmailStatus: string
{
    case RECEIVED = 'received';
    case MATCHED = 'matched';
    case NEEDS_REVIEW = 'needs_review';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::RECEIVED => 'Received',
            self::MATCHED => 'Matched to Order',
            self::NEEDS_REVIEW => 'Needs Review',
            self::ARCHIVED => 'Archived',
        };
    }
}
