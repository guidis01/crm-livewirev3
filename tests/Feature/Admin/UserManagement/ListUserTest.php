<?php

use App\Enum\Can;
use App\Livewire\Admin;
use App\Models\{Permission, User};
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
                ['key' => 'id', 'label' => '#', 'sortColumnBy' => 'id', 'sortDirection' => 'asc'],
                ['key' => 'name', 'label' => 'Name', 'sortColumnBy' => 'id', 'sortDirection' => 'asc'],
                ['key' => 'email', 'label' => 'Email', 'sortColumnBy' => 'id', 'sortDirection' => 'asc'],
                ['key' => 'permissions', 'label' => 'Permissions', 'sortColumnBy' => 'id', 'sortDirection' => 'asc'],
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

it('should be able to filter by permission.key', function () {
    $admin       = User::factory()->admin()->create(['name' => 'John Doe', 'email' => 'john@globalhitss.com.br']);
    $jane        = User::factory()->withPermission(Can::TESTING)->create(['name' => 'Jane Doe', 'email' => 'jani@globalhitss.com.br']);
    $permission  = Permission::where('key', '=', Can::BE_AN_ADMIN->value)->first();
    $permission2 = Permission::where('key', '=', Can::TESTING->value)->first();

    actingAs($admin);

    Livewire::test('admin.users.index')
        ->assertSet('users', function ($users) {
            expect($users)->toHaveCount(2);

            return true;
        })
        ->set('search_permissions', [$permission->id, $permission2->id])
        ->assertSet('users', function ($users) {
            expect($users)
                ->toHaveCount(2)
                ->first()->name->toBe('John Doe');

            return true;
        });
});

it('should be able to list deleted users', function () {
    $admin        = User::factory()->admin()->create(['name' => 'John Doe', 'email' => 'john@globalhitss.com.br']);
    $deletedUsers = User::factory()->count(2)->create(['deleted_at' => now()]);

    actingAs($admin);

    Livewire::test('admin.users.index')
        ->assertSet('users', function ($users) {
            expect($users)->toHaveCount(1);

            return true;
        })
        ->set('search_trash', true)
        ->assertSet('users', function ($users) {
            expect($users)
                ->toHaveCount(2);

            return true;
        });
});

it('should be able to sort by name', function () {
    $admin = User::factory()->admin()->create(['name' => 'John Doe', 'email' => 'john@globalhitss.com.br']);
    $jane  = User::factory()->withPermission(Can::TESTING)->create(['name' => 'Jane Doe', 'email' => 'jani@globalhitss.com.br']);

    actingAs($admin);

    Livewire::test('admin.users.index')
        ->set('sortDirection', 'asc')
        ->set('sortColumnBy', 'name')
        ->assertSet('users', function ($users) {
            expect($users)
                ->first()->name->toBe('Jane Doe')
                ->and($users)->last()->name->toBe('John Doe');

            return true;
        })->set('sortDirection', 'desc')
        ->set('sortColumnBy', 'name')
        ->assertSet('users', function ($users) {
            expect($users)
                ->first()->name->toBe('John Doe')
                ->and($users)->last()->name->toBe('Jane Doe');

            return true;
        });
});
