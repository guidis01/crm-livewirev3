<?php

use App\Livewire\Admin;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, get};

it('should be able to access the route admin/users', function () {

    actingAs(User::factory()->admin()->create());

    get(route('admin.users'))
        ->assertOk();
});

test('making sure that the route is protected by the permission BE_AN_ADMIN', function () {
    actingAs(User::factory()->create());

    get(route('admin.users'))
        ->assertForbidden();
});

it("let's create a livewire component to list all users in the page", function () {
    actingAs(User::factory()->admin()->create());
    $users = User::factory()->count(10)->create();

    $lw = Livewire::test(Admin\Users\Index::class);

    $lw->assertSet('users', function ($users) {
        expect($users)
            ->toHaveCount(11);

        return true;
    });

    foreach ($users as $user) {
        $lw->assertSee($user->name);
    }

});

test('check the table format', function () {
    actingAs(User::factory()->admin()->create());
    Livewire::test(App\Livewire\Admin\Users\Index::class)
        ->assertSet(
            'headers',
            [
                ['key' => 'id', 'label' => '#'],
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'permissions', 'label' => 'Permissions'],
            ]
        );
});

it('should be able to filter by name and email', function () {
    $admin = User::factory()->admin()->create(['name' => 'John Doe', 'email' => 'john@globalhitss.com.br']);
    $jane  = User::factory()->create(['name' => 'Jane Doe', 'email' => 'jani@globalhitss.com.br']);

    actingAs($admin);

    Livewire::test('admin.users.index')
        ->assertSet('users', function ($users) {
            expect($users)->toHaveCount(2);

            return true;
        })
        ->set('search', 'jan')
        ->assertSet('users', function ($users) {
            expect($users)
                ->toHaveCount(1)
                ->first()->name->toBe('Jane Doe');

            return true;
        })->set('search', 'jani')
        ->assertSet('users', function ($users) {
            expect($users)
                ->toHaveCount(1)
                ->first()->name->toBe('Jane Doe');

            return true;
        });
});
