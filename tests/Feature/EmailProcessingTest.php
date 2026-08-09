<?php

use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use App\Services\EmailProcessingService;
use App\Services\OrderService;

test('incoming email auto-extracts 17-character VIN and links to order', function () {
    $customer = Customer::create(['name' => 'Automated Customer', 'phone' => '+1 555-7777']);

    $orderService = new OrderService;
    $order = $orderService->createOrder([
        'customer_id' => $customer->id,
        'vin' => '1FA6P8CF0H5666666',
        'make' => 'Ford',
        'model' => 'Mustang',
        'year' => 2022,
    ]);

    $emailService = new EmailProcessingService;
    $email = $emailService->processIncomingEmail([
        'sender' => 'shipping@grimaldi.com',
        'subject' => 'Vessel Gate In Confirmation for VIN 1FA6P8CF0H5666666',
        'body' => 'Your vehicle has passed port gate inspection.',
        'attachments' => [
            ['filename' => 'GateReceipt.pdf', 'file_size' => 102400],
        ],
    ]);

    expect($email->order_id)->toBe($order->id);
    expect($email->processing_status->value)->toBe('matched');
    expect($order->timelineEvents()->where('event_type', 'email_received')->count())->toBeGreaterThan(0);
});

test('staff can manually link an unassigned email to an order via route without errors', function () {
    $adminRole = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
    $user = User::factory()->create(['role_id' => $adminRole->id]);

    $customer = Customer::create(['name' => 'Manual Customer', 'phone' => '+1 555-9900']);

    $orderService = new OrderService;
    $order = $orderService->createOrder([
        'customer_id' => $customer->id,
        'vin' => '1FA6P8CF0H5555555',
        'make' => 'Ford',
        'model' => 'Mustang',
        'year' => 2022,
    ]);

    $emailService = new EmailProcessingService;
    $email = $emailService->processIncomingEmail([
        'sender' => 'unknown@port.com',
        'subject' => 'Unidentified Shipping Receipt',
        'body' => 'Please match manually.',
    ]);

    $response = $this->actingAs($user)->post("/emails/{$email->id}/link", [
        'order_id' => $order->id,
    ]);

    $response->assertRedirect();

    $email->refresh();
    expect($email->order_id)->toBe($order->id);
    expect($email->processing_status->value)->toBe('matched');
});

test('staff can link an email with attachments and automatically import selected files as Order Documents', function () {
    $adminRole = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
    $user = User::factory()->create(['role_id' => $adminRole->id]);

    $customer = Customer::create(['name' => 'Attachment Customer', 'phone' => '+1 555-8888']);

    $orderService = new OrderService;
    $order = $orderService->createOrder([
        'customer_id' => $customer->id,
        'vin' => '5NPE34AB4FH169289',
        'make' => 'Hyundai',
        'model' => 'Sonata',
        'year' => 2021,
    ]);

    $emailService = new EmailProcessingService;
    $email = $emailService->processIncomingEmail([
        'sender' => 'operations@ankshipping.com',
        'subject' => 'ANK Shipping Manifest & Invoice',
        'body' => 'Please find attached BL and Invoice documents.',
        'attachments' => [
            ['filename' => 'BL_5NPE34AB4FH169289.pdf', 'file_size' => 59422],
            ['filename' => 'Invoice_ANK00040.pdf', 'file_size' => 205174],
        ],
    ]);

    $attachmentIds = $email->attachments->pluck('id')->toArray();

    $response = $this->actingAs($user)->post("/emails/{$email->id}/link", [
        'order_id' => $order->id,
        'attachment_document_types' => [
            $attachmentIds[0] => 'bill_of_lading',
            $attachmentIds[1] => 'invoice',
        ],
    ]);

    $response->assertRedirect();

    $email->refresh();
    expect($email->order_id)->toBe($order->id);
    expect($order->documents()->count())->toBe(2);

    $bolDoc = $order->documents()->where('document_type', 'bill_of_lading')->first();
    expect($bolDoc)->not->toBeNull();
    expect($bolDoc->file_name)->toBe('BL_5NPE34AB4FH169289.pdf');

    $invDoc = $order->documents()->where('document_type', 'invoice')->first();
    expect($invDoc)->not->toBeNull();
    expect($invDoc->file_name)->toBe('Invoice_ANK00040.pdf');
});
