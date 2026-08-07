<?php

namespace App\Enums;

enum DocumentType: string
{
    case BILL_OF_LADING = 'bill_of_lading';
    case DOCK_RECEIPT = 'dock_receipt';
    case INVOICE = 'invoice';
    case TELEX_RELEASE = 'telex_release';
    case TITLE = 'title';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::BILL_OF_LADING => 'Bill of Lading',
            self::DOCK_RECEIPT => 'Dock Receipt',
            self::INVOICE => 'Invoice Document',
            self::TELEX_RELEASE => 'Telex Release (Printable Text)',
            self::TITLE => 'Vehicle Title',
            self::OTHER => 'Other Document',
        };
    }
}
