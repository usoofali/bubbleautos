<?php

use App\Models\Customer;
use App\Models\Document;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('document upload stores file and deleting permanently removes file from disk', function () {
    Storage::fake('local');

    $adminRole = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
    $user = User::factory()->create(['role_id' => $adminRole->id]);

    $customer = Customer::create(['name' => 'Doc Test', 'phone' => '+1 555-2211']);
    $order = Order::create([
        'order_number' => 'BA-00099',
        'vin' => '1FA6P8CF0H5333333',
        'make' => 'Ford',
        'model' => 'Mustang',
        'year' => 2022,
        'customer_id' => $customer->id,
    ]);

    $file = UploadedFile::fake()->create('sample_title.pdf', 500, 'application/pdf');

    $response = $this->actingAs($user)->post("/orders/{$order->id}/documents", [
        'title' => 'Texas Title Document',
        'document_type' => 'title',
        'file' => $file,
    ]);

    $response->assertRedirect();

    $doc = Document::where('title', 'Texas Title Document')->first();
    expect($doc)->not->toBeNull();
    Storage::disk('local')->assertExists($doc->file_path);

    // Delete Document
    $response = $this->actingAs($user)->delete("/documents/{$doc->id}");
    $response->assertRedirect();
    Storage::disk('local')->assertMissing($doc->file_path);
    expect(Document::find($doc->id))->toBeNull();
});
