<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;

test('staff can create order with auto-generated order number and auto-created blank invoice', function () {
    $adminRole = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
    $user = User::factory()->create(['role_id' => $adminRole->id]);

    $customer = Customer::create([
        'name' => 'Mark Taylor',
        'phone' => '+1 555-4433',
    ]);

    $response = $this->actingAs($user)->post('/orders', [
        'customer_id' => $customer->id,
        'vin' => '5YJ3E1EA1KF888888',
        'make' => 'Tesla',
        'model' => 'Model 3',
        'year' => 2023,
        'color' => 'Pearl White',
        'shipping_line' => 'Grimaldi Lines',
        'destination' => 'Lagos, Nigeria',
    ]);

    $response->assertRedirect();

    $order = Order::where('vin', '5YJ3E1EA1KF888888')->first();

    expect($order)->not->toBeNull();
    expect($order->order_number)->toBe('BA-00001');
    expect($order->invoice)->not->toBeNull();
    expect((float) $order->invoice->total)->toEqual(0);
    expect($order->timelineEvents)->not->toBeEmpty();
});
