<?php

use App\Models\{Permission, User};
use Database\Seeders\{PermissionSeeder, UsersSeeder};

use function Pest\Laravel\{actingAs, assertDatabaseHas, get, seed};

it('should be able to give an user a permission to do something', function () {
    /** var User @user */
    $user = User::factory()->create();

    $user->givePermissionTo('be an admin');

    expect($user)
        ->hasPermissionTo('be an admin')
        ->toBeTrue();

    assertDatabaseHas('permissions', [
        'key' => 'be an admin',
    ]);

    assertDatabaseHas('permission_user', [
        'user_id'       => $user->id,
        'permission_id' => Permission::query()->where(['key' => 'be an admin'])->first()->id,
    ]);
});

test('permission has to have a seeder', function () {
    seed(PermissionSeeder::class);

    assertDatabaseHas('permissions', [
        'key' => 'be an admin',
    ]);
});

test('seed with an admin user', function () {
    seed([PermissionSeeder::class, UsersSeeder::class]);

    assertDatabaseHas('permissions', [
        'key' => 'be an admin',
    ]);

    assertDatabaseHas('permission_user', [
        'user_id'       => User::first()?->id,
        'permission_id' => Permission::query()->where(['key' => 'be an admin'])->first()?->id,
    ]);
});

test('it should block the access to an admin page if the user does not have the permission to be an admin', closure: function () {
    $user = User::factory()->create();

    actingAs($user);

    get(route('admin.dashboard'))
        ->assertForbidden();
});
