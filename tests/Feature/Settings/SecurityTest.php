<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('admin can update password via security settings', function () {
    $adminRole = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    $response = $this
        ->actingAs($admin)
        ->from(route('security.edit'))
        ->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('security.edit'));

    expect(Hash::check('new-password', $admin->refresh()->password))->toBeTrue();
});

test('non-admin staff cannot update password via security settings', function () {
    $staffRole = Role::create(['name' => 'Staff', 'slug' => 'staff']);
    $staff = User::factory()->create(['role_id' => $staffRole->id]);

    $response = $this
        ->actingAs($staff)
        ->from(route('security.edit'))
        ->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasErrors('current_password')
        ->assertRedirect(route('security.edit'));
});
