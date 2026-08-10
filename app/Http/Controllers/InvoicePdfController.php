<?php

namespace App\Http\Controllers;

use App\Models\InvoicePayment;
use App\Models\Order;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class InvoicePdfController extends Controller
{
    /**
     * Generate and download the official Invoice PDF.
     */
    public function downloadInvoice(Order $order): Response
    {
        $this->authorize('view', $order);

        $order->load([
            'customer',
            'invoice.items',
        ]);

        $companySettings = [
            'name' => Setting::get('company_name', 'Bubbles Autos'),
            'logo' => Setting::get('company_logo', '/logo.jpeg'),
            'address' => Setting::get('contact_address', '100 Shipping Way, Houston, TX 77001'),
            'email' => Setting::get('contact_email', 'contact@bubbleautos.com'),
            'phone' => Setting::get('contact_phone', '+1 (800) 555-BUBBLE'),
            'currency_symbol' => Setting::get('currency_symbol', '$'),
            'currency_code' => Setting::get('currency_code', 'USD'),
        ];

        $pdf = Pdf::loadView('pdf.invoice', [
            'order' => $order,
            'companySettings' => $companySettings,
        ])->setPaper('a4', 'portrait');

        $filename = 'Invoice-'.$order->order_number.'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Generate and download the official Payment Receipt PDF.
     */
    public function downloadReceipt(InvoicePayment $payment): Response
    {
        $payment->load([
            'recorder',
            'invoice.order.customer',
        ]);

        $order = $payment->invoice->order;

        $companySettings = [
            'name' => Setting::get('company_name', 'Bubbles Autos'),
            'logo' => Setting::get('company_logo', '/logo.jpeg'),
            'address' => Setting::get('contact_address', '100 Shipping Way, Houston, TX 77001'),
            'email' => Setting::get('contact_email', 'contact@bubbleautos.com'),
            'phone' => Setting::get('contact_phone', '+1 (800) 555-BUBBLE'),
            'currency_symbol' => Setting::get('currency_symbol', '$'),
            'currency_code' => Setting::get('currency_code', 'USD'),
        ];

        $pdf = Pdf::loadView('pdf.receipt', [
            'payment' => $payment,
            'order' => $order,
            'companySettings' => $companySettings,
        ])->setPaper('a4', 'portrait');

        $ref = $payment->reference ? str_replace('/', '-', $payment->reference) : 'REC-'.$payment->id;
        $filename = 'Receipt-'.$ref.'.pdf';

        return $pdf->download($filename);
    }
}
