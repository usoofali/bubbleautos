<?php

namespace App\Services;

use App\Enums\TimelineEventType;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function recordPayment(Invoice $invoice, array $paymentData): Payment
    {
        return DB::transaction(function () use ($invoice, $paymentData) {
            $payment = $invoice->payments()->create([
                'amount' => $paymentData['amount'],
                'payment_date' => $paymentData['payment_date'] ?? now()->toDateString(),
                'method' => $paymentData['method'],
                'reference' => $paymentData['reference'] ?? null,
                'notes' => $paymentData['notes'] ?? null,
                'recorded_by' => Auth::id(),
            ]);

            $invoice->recalculate();

            TimelineService::log(
                $invoice->order,
                TimelineEventType::PAYMENT_RECEIVED,
                'Payment Received',
                "Recorded payment of {$invoice->currency}{$payment->amount} via ".str_replace('_', ' ', strtoupper($payment->method->value)).". Remaining Balance: {$invoice->currency}{$invoice->balance}"
            );

            ActivityLogService::log(
                'payment.recorded',
                "Recorded payment {$invoice->currency}{$payment->amount} for invoice {$invoice->invoice_number}",
                Payment::class,
                $payment->id
            );

            return $payment;
        });
    }

    public function deletePayment(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $invoice = $payment->invoice;
            $amount = $payment->amount;

            $payment->delete();
            $invoice->recalculate();

            TimelineService::log(
                $invoice->order,
                TimelineEventType::INVOICE_UPDATED,
                'Payment Cancelled / Removed',
                "Removed payment entry of {$invoice->currency}{$amount}. Updated Balance: {$invoice->currency}{$invoice->balance}"
            );

            ActivityLogService::log(
                'payment.deleted',
                "Deleted payment {$invoice->currency}{$amount} from invoice {$invoice->invoice_number}",
                Payment::class,
                $payment->id
            );
        });
    }
}
