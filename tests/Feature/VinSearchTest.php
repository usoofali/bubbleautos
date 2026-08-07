<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;

test('vin search executes under 1 second and finds matching vehicle orders', function () {
    $user = User::factory()->create();
    $customer = Customer::create([
        'name' => 'Jane Speed',
        'phone' => '+1 555-9988',
    ]);

    $order = Order::create([
        'order_number' => 'BA-00001',
        'vin' => '1FA6P8CF0H5999999',
        'make' => 'Ford',
        'model' => 'Mustang GT',
        'year' => 2022,
        'customer_id' => $customer->id,
    ]);

    $startTime = microtime(true);

    $response = $this->actingAs($user)->getJson('/api/search?q=1FA6P8CF0H5999999');

    $executionTime = microtime(true) - $startTime;

    $response->assertStatus(200);
    $response->assertJsonFragment(['vin' => '1FA6P8CF0H5999999']);
    expect($executionTime)->toBeLessThan(1.0);
});
