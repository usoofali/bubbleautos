<?php

namespace App\Services;

use App\Enums\TimelineEventType;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function addItem(Invoice $invoice, array $itemData): InvoiceItem
    {
        return DB::transaction(function () use ($invoice, $itemData) {
            $amount = (float) $itemData['amount'];

            $item = $invoice->items()->create([
                'description' => $itemData['description'],
                'quantity' => 1,
                'unit_price' => $amount,
                'amount' => $amount,
            ]);

            $invoice->recalculate();

            TimelineService::log(
                $invoice->order,
                TimelineEventType::INVOICE_UPDATED,
                'Invoice Item Added',
                "Added '{$item->description}' ({$invoice->currency}{$amount}). New Total: {$invoice->currency}{$invoice->total}"
            );

            ActivityLogService::log(
                'invoice.item_added',
                "Added item '{$item->description}' to invoice {$invoice->invoice_number}",
                Invoice::class,
                $invoice->id
            );

            return $item;
        });
    }

    public function updateItem(InvoiceItem $item, array $itemData): InvoiceItem
    {
        return DB::transaction(function () use ($item, $itemData) {
            $amount = (float) $itemData['amount'];

            $item->update([
                'description' => $itemData['description'],
                'quantity' => 1,
                'unit_price' => $amount,
                'amount' => $amount,
            ]);

            $invoice = $item->invoice;
            $invoice->recalculate();

            TimelineService::log(
                $invoice->order,
                TimelineEventType::INVOICE_UPDATED,
                'Invoice Item Updated',
                "Updated item '{$item->description}' to {$invoice->currency}{$amount}. New Total: {$invoice->currency}{$invoice->total}"
            );

            ActivityLogService::log(
                'invoice.item_updated',
                "Updated item '{$item->description}' on invoice {$invoice->invoice_number}",
                Invoice::class,
                $invoice->id
            );

            return $item;
        });
    }

    public function removeItem(InvoiceItem $item): void
    {
        DB::transaction(function () use ($item) {
            $invoice = $item->invoice;
            $description = $item->description;

            $item->delete();
            $invoice->recalculate();

            TimelineService::log(
                $invoice->order,
                TimelineEventType::INVOICE_UPDATED,
                'Invoice Item Removed',
                "Removed '{$description}'. Updated Total: {$invoice->currency}{$invoice->total}"
            );

            ActivityLogService::log(
                'invoice.item_removed',
                "Removed item '{$description}' from invoice {$invoice->invoice_number}",
                Invoice::class,
                $invoice->id
            );
        });
    }
}
