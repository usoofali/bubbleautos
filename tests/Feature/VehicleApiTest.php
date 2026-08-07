<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use App\Services\VehicleApiService;
use Illuminate\Support\Facades\Http;

test('vehicle api service resolves vehicle make model year and pictures by vin', function () {
    Http::fake([
        'https://app.ankshipping.com/api/vehicles/1HGCR2F83HA123456/pictures' => Http::response([
            'success' => true,
            'vin' => '1HGCR2F33FA169823',
            'make' => 'Honda',
            'model' => 'Accord',
            'year' => '2017',
            'pictures' => [
                'https://cs.copart.com/v1/AUTH_svc/images/photo1.jpg',
                'https://cs.copart.com/v1/AUTH_svc/images/photo2.jpg',
            ],
        ], 200),
    ]);

    $service = new VehicleApiService;
    $result = $service->lookupByVin('1HGCR2F83HA123456');

    expect($result['success'])->toBeTrue();
    expect($result['make'])->toBe('Honda');
    expect($result['model'])->toBe('Accord');
    expect($result['year'])->toBe(2017);
    expect($result['pictures'])->toHaveCount(2);
});

test('syncing order vehicle api data updates order model specs and pictures', function () {
    Http::fake([
        'https://app.ankshipping.com/api/vehicles/1HGCR2F83HA123456/pictures' => Http::response([
            'success' => true,
            'vin' => '1HGCR2F83HA123456',
            'make' => 'Honda',
            'model' => 'Accord',
            'year' => '2017',
            'pictures' => [
                'https://cs.copart.com/v1/AUTH_svc/images/photo1.jpg',
            ],
        ], 200),
    ]);

    $adminRole = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
    $user = User::factory()->create(['role_id' => $adminRole->id]);
    $customer = Customer::create(['name' => 'Jane Doe', 'phone' => '+1 555-9090']);

    $order = Order::create([
        'order_number' => 'BA-00099',
        'vin' => '1HGCR2F83HA123456',
        'customer_id' => $customer->id,
    ]);

    $response = $this->actingAs($user)->post(route('orders.sync-vehicle', $order->id));
    $response->assertRedirect();

    $order->refresh();
    expect($order->make)->toBe('Honda');
    expect($order->model)->toBe('Accord');
    expect($order->year)->toBe(2017);
    expect($order->pictures)->toHaveCount(1);
});
