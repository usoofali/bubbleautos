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
