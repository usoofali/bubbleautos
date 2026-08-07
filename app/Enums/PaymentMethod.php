<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case BANK_TRANSFER = 'bank_transfer';
    case CASH = 'cash';
    case CARD = 'card';
    case CHEQUE = 'cheque';
    case ZELLE = 'zelle';
    case WIRE = 'wire';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::BANK_TRANSFER => 'Bank Transfer',
            self::CASH => 'Cash',
            self::CARD => 'Credit / Debit Card',
            self::CHEQUE => 'Cheque',
            self::ZELLE => 'Zelle',
            self::WIRE => 'Wire Transfer',
            self::OTHER => 'Other',
        };
    }
}
