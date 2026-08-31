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

test('staff with permission can permanently delete an order', function () {
    $adminRole = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
    $user = User::factory()->create(['role_id' => $adminRole->id]);

    $customer = Customer::create([
        'name' => 'John Doe',
        'phone' => '+1 555-1234',
    ]);

    $order = Order::create([
        'order_number' => 'BA-00002',
        'vin' => '1FA6P8CF0H5999999',
        'customer_id' => $customer->id,
    ]);

    $response = $this->actingAs($user)->delete("/orders/{$order->id}");

    $response->assertRedirect('/orders');

    $this->assertDatabaseMissing('orders', [
        'id' => $order->id,
    ]);
});

test('staff can create order by registering a new inline customer', function () {
    $adminRole = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
    $user = User::factory()->create(['role_id' => $adminRole->id]);

    $response = $this->actingAs($user)->post('/orders', [
        'new_customer' => [
            'name' => 'Jane Smith',
            'phone' => '+1 555-9876',
            'email' => 'jane@example.com',
        ],
        'vin' => '1FA6P8CF0H5111111',
        'make' => 'Ford',
        'model' => 'Mustang',
        'year' => 2022,
    ]);

    $response->assertRedirect();

    $customer = Customer::where('name', 'Jane Smith')->first();
    expect($customer)->not->toBeNull();

    $order = Order::where('vin', '1FA6P8CF0H5111111')->first();
    expect($order)->not->toBeNull();
    expect($order->customer_id)->toBe($customer->id);
});

test('staff can view orders listing page with total orders and status metrics', function () {
    $adminRole = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
    $user = User::factory()->create(['role_id' => $adminRole->id]);

    $customer = Customer::create(['name' => 'Metrics Test Customer', 'phone' => '+1 555-0000']);
    Order::create([
        'order_number' => 'BA-00003',
        'vin' => '1FA6P8CF0H5222222',
        'customer_id' => $customer->id,
        'status' => 'in_transit',
    ]);

    $response = $this->actingAs($user)->get('/orders');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Orders/Index')
        ->has('stats', fn ($stats) => $stats
            ->where('total', 1)
            ->where('this_month', 1)
            ->where('in_transit', 1)
            ->where('delivered', 0)
        )
    );
});
