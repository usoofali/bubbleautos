<?php

namespace App\Enums;

enum TimelineEventType: string
{
    case ORDER_CREATED = 'order_created';
    case SHIPMENT_STATUS_UPDATED = 'shipment_status_updated';
    case EMAIL_RECEIVED = 'email_received';
    case DOCUMENT_UPLOADED = 'document_uploaded';
    case INVOICE_UPDATED = 'invoice_updated';
    case PAYMENT_RECEIVED = 'payment_received';
    case NOTE_ADDED = 'note_added';
    case MANUAL_UPDATE = 'manual_update';

    public function label(): string
    {
        return match ($this) {
            self::ORDER_CREATED => 'Order Created',
            self::SHIPMENT_STATUS_UPDATED => 'Shipment Status Updated',
            self::EMAIL_RECEIVED => 'Email Received',
            self::DOCUMENT_UPLOADED => 'Document Uploaded',
            self::INVOICE_UPDATED => 'Invoice Updated',
            self::PAYMENT_RECEIVED => 'Payment Received',
            self::NOTE_ADDED => 'Note Added',
            self::MANUAL_UPDATE => 'Manual Update',
        };
    }
}
