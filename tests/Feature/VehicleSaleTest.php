<?php

use App\Models\User;
use App\Models\VehicleSale;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can view vehicle sales index page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/vehicle-sales');

    $response->assertStatus(200);
});

test('user can record a new vehicle sale and initial payment receipt', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/vehicle-sales', [
        'customer_name' => 'Alhaji Aminu Kano',
        'customer_phone' => '08033473516',
        'customer_address' => 'No 45 Nassarawa GRA, Kano',
        'vehicle_make' => 'Mercedes-Benz',
        'vehicle_model' => 'GLE 450',
        'vehicle_year' => '2024',
        'vehicle_vin' => 'WDC1671591A123456',
        'vehicle_color' => 'Black',
        'vehicle_description' => 'Full Option V6 Turbo',
        'sale_date' => '2026-08-10',
        'sale_amount' => 85000000,
        'amount_paid' => 50000000,
        'payment_method' => 'bank_transfer',
    ]);

    $this->assertDatabaseHas('vehicle_sales', [
        'customer_name' => 'Alhaji Aminu Kano',
        'vehicle_make' => 'Mercedes-Benz',
        'vehicle_model' => 'GLE 450',
        'sale_number' => '0001',
    ]);

    $sale = VehicleSale::where('sale_number', '0001')->first();

    $this->assertDatabaseHas('vehicle_sale_payments', [
        'vehicle_sale_id' => $sale->id,
        'amount_paid' => 50000000,
    ]);

    expect($sale->payment_status)->toBe('partially_paid');

    $response->assertRedirect('/vehicle-sales/'.$sale->id);
});

test('user can record subsequent payment installment for a vehicle sale', function () {
    $user = User::factory()->create();
    $sale = VehicleSale::create([
        'sale_number' => '0001',
        'customer_name' => 'John Doe',
        'vehicle_make' => 'Toyota',
        'vehicle_model' => 'Camry',
        'sale_date' => '2026-08-10',
        'sale_amount' => 85000000,
        'amount_paid' => 50000000,
    ]);

    $sale->payments()->create([
        'receipt_number' => '0001',
        'amount_paid' => 50000000,
        'payment_date' => '2026-08-10',
        'payment_method' => 'bank_transfer',
    ]);

    $response = $this->actingAs($user)->post("/vehicle-sales/{$sale->id}/payments", [
        'amount_paid' => 35000000,
        'payment_date' => '2026-08-11',
        'payment_method' => 'bank_transfer',
        'amount_in_words' => 'Thirty-Five Million Naira Only',
    ]);

    $sale->refresh();

    expect($sale->payments->count())->toBe(2);
    expect($sale->total_paid)->toBe(85000000.0);
    expect($sale->payment_status)->toBe('paid');

    $response->assertRedirect('/vehicle-sales/'.$sale->id);
});

test('user can download invoice pdf for vehicle sale', function () {
    $user = User::factory()->create();
    $sale = VehicleSale::create([
        'sale_number' => '0001',
        'customer_name' => 'John Doe',
        'vehicle_make' => 'Toyota',
        'vehicle_model' => 'Camry',
        'sale_date' => '2026-08-10',
        'sale_amount' => 35000000,
        'amount_paid' => 35000000,
    ]);

    $response = $this->actingAs($user)->get("/vehicle-sales/{$sale->id}/invoice/pdf");

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('user can download cash receipt pdf for a specific payment installment', function () {
    $user = User::factory()->create();
    $sale = VehicleSale::create([
        'sale_number' => '0001',
        'customer_name' => 'John Doe',
        'vehicle_make' => 'Toyota',
        'vehicle_model' => 'Camry',
        'sale_date' => '2026-08-10',
        'sale_amount' => 35000000,
        'amount_paid' => 35000000,
    ]);

    $payment = $sale->payments()->create([
        'receipt_number' => '0001',
        'amount_paid' => 35000000,
        'payment_date' => '2026-08-10',
        'payment_method' => 'bank_transfer',
    ]);

    $response = $this->actingAs($user)->get("/vehicle-sales/{$sale->id}/payments/{$payment->id}/receipt/pdf");

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('user can delete vehicle sale and its payment receipts', function () {
    $user = User::factory()->create();
    $sale = VehicleSale::create([
        'sale_number' => '0001',
        'customer_name' => 'John Doe',
        'vehicle_make' => 'Toyota',
        'vehicle_model' => 'Camry',
        'sale_date' => '2026-08-10',
        'sale_amount' => 35000000,
        'amount_paid' => 35000000,
    ]);

    $payment = $sale->payments()->create([
        'receipt_number' => '0001',
        'amount_paid' => 35000000,
        'payment_date' => '2026-08-10',
        'payment_method' => 'bank_transfer',
    ]);

    $response = $this->actingAs($user)->delete("/vehicle-sales/{$sale->id}");

    $response->assertRedirect('/vehicle-sales');

    $this->assertSoftDeleted('vehicle_sales', [
        'id' => $sale->id,
    ]);

    $this->assertDatabaseMissing('vehicle_sale_payments', [
        'id' => $payment->id,
    ]);
});
