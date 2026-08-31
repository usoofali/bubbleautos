<?php

use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\OrderService;
use App\Services\PaymentService;

test('adding and updating line items recalculates invoice totals and partial payments update balance', function () {
    $adminRole = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
    $user = User::factory()->create(['role_id' => $adminRole->id]);

    $customer = Customer::create(['name' => 'Bill Gates', 'phone' => '+1 555-1010']);

    $orderService = new OrderService;
    $order = $orderService->createOrder([
        'customer_id' => $customer->id,
        'vin' => 'JTDKN3DU4F0777777',
        'make' => 'Toyota',
        'model' => 'Camry',
        'year' => 2021,
    ]);

    $invoice = $order->invoice;

    $invoiceService = new InvoiceService;
    $item1 = $invoiceService->addItem($invoice, ['description' => 'Freight Charge', 'amount' => 1200]);
    $item2 = $invoiceService->addItem($invoice, ['description' => 'Customs Title Handling', 'amount' => 300]);

    $invoice->refresh();

    expect((float) $invoice->subtotal)->toBe(1500.0);
    expect((float) $invoice->total)->toBe(1500.0);
    expect((float) $invoice->balance)->toBe(1500.0);
    expect($invoice->status->value)->toBe('pending');

    // Update item 2
    $invoiceService->updateItem($item2, ['description' => 'Customs Title Handling Revised', 'amount' => 250]);
    $invoice->refresh();

    expect((float) $invoice->total)->toBe(1450.0);

    // Partial Payment
    $paymentService = new PaymentService;
    $paymentService->recordPayment($invoice, [
        'amount' => 500.00,
        'payment_date' => now()->toDateString(),
        'method' => 'bank_transfer',
        'reference' => 'TXN-901',
    ]);

    $invoice->refresh();

    expect((float) $invoice->paid)->toBe(500.0);
    expect((float) $invoice->balance)->toBe(950.0);
    expect($invoice->status->value)->toBe('partially_paid');
});

test('staff can download styled pdf invoice and payment receipt', function () {
    $adminRole = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
    $user = User::factory()->create(['role_id' => $adminRole->id]);

    $customer = Customer::create(['name' => 'PDF Test Customer', 'phone' => '+1 555-2020']);
    $orderService = new OrderService;
    $order = $orderService->createOrder([
        'customer_id' => $customer->id,
        'vin' => '1FA6P8CF0H5333333',
        'make' => 'Chevrolet',
        'model' => 'Tahoe',
        'year' => 2022,
    ]);

    $invoiceService = new InvoiceService;
    $invoice = $order->invoice;
    $invoiceService->addItem($invoice, ['description' => 'Ocean Freight', 'amount' => 1800]);

    // Test invoice PDF download
    $invoiceResponse = $this->actingAs($user)->get("/orders/{$order->id}/invoice/pdf");
    $invoiceResponse->assertOk();
    $invoiceResponse->assertHeader('content-type', 'application/pdf');

    // Record payment
    $paymentService = new PaymentService;
    $payment = $paymentService->recordPayment($invoice, [
        'amount' => 1000.00,
        'payment_date' => now()->toDateString(),
        'method' => 'bank_transfer',
        'reference' => 'TXN-PDF-01',
    ]);

    // Test receipt PDF download
    $receiptResponse = $this->actingAs($user)->get("/invoices/payments/{$payment->id}/pdf");
    $receiptResponse->assertOk();
    $receiptResponse->assertHeader('content-type', 'application/pdf');
});
