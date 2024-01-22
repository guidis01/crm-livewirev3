<?php

use App\Enum\Can;
use App\Livewire\Customers;
use App\Models\{Customer, Permission, User};
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, get};

it('should be able to access the route customers', function () {

    actingAs(User::factory()->admin()->create());

    get(route('customers'))
        ->assertOk();
});

it("let's create a livewire component to list all customers in the page", function () {
    actingAs(User::factory()->create());
    $customers = Customer::factory()->count(10)->create();

    $lw = Livewire::test(Customers\Index::class);

    $lw->assertSet('customers', function ($customers) {
        expect($customers)
            ->toHaveCount(10);

        return true;
    });

    foreach ($customers as $customer) {
        $lw->assertSee($customer->name);
    }

});

test('check the table format', function () {
    actingAs(User::factory()->admin()->create());
    Livewire::test(App\Livewire\Customers\Index::class)
        ->assertSet(
            'headers',
            [
                ['key' => 'id', 'label' => '#', 'sortColumnBy' => 'id', 'sortDirection' => 'asc'],
                ['key' => 'name', 'label' => 'Name', 'sortColumnBy' => 'id', 'sortDirection' => 'asc'],
                ['key' => 'email', 'label' => 'Email', 'sortColumnBy' => 'id', 'sortDirection' => 'asc'],
            ]
        );
});

it('should be able to filter by name and email', function () {
    $admin = User::factory()->admin()->create(['name' => 'John Doe', 'email' => 'john@globalhitss.com.br']);
    $jane  = User::factory()->create(['name' => 'Jane Doe', 'email' => 'jani@globalhitss.com.br']);

    actingAs($admin);

    Livewire::test('admin.customers.index')
        ->assertSet('customers', function ($customers) {
            expect($customers)->toHaveCount(2);

            return true;
        })
        ->set('search', 'jan')
        ->assertSet('customers', function ($customers) {
            expect($customers)
                ->toHaveCount(1)
                ->first()->name->toBe('Jane Doe');

            return true;
        })->set('search', 'jani')
        ->assertSet('customers', function ($customers) {
            expect($customers)
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

    Livewire::test('admin.customers.index')
        ->assertSet('customers', function ($customers) {
            expect($customers)->toHaveCount(2);

            return true;
        })
        ->set('search_permissions', [$permission->id, $permission2->id])
        ->assertSet('customers', function ($customers) {
            expect($customers)
                ->toHaveCount(2)
                ->first()->name->toBe('John Doe');

            return true;
        });
});

it('should be able to list deleted customers', function () {
    $admin        = User::factory()->admin()->create(['name' => 'John Doe', 'email' => 'john@globalhitss.com.br']);
    $deletedUsers = User::factory()->count(2)->create(['deleted_at' => now()]);

    actingAs($admin);

    Livewire::test('admin.customers.index')
        ->assertSet('customers', function ($customers) {
            expect($customers)->toHaveCount(1);

            return true;
        })
        ->set('search_trash', true)
        ->assertSet('customers', function ($customers) {
            expect($customers)
                ->toHaveCount(2);

            return true;
        });
});

it('should be able to sort by name', function () {
    $admin = User::factory()->admin()->create(['name' => 'John Doe', 'email' => 'john@globalhitss.com.br']);
    $jane  = User::factory()->withPermission(Can::TESTING)->create(['name' => 'Jane Doe', 'email' => 'jani@globalhitss.com.br']);

    actingAs($admin);

    Livewire::test('admin.customers.index')
        ->set('sortDirection', 'asc')
        ->set('sortColumnBy', 'name')
        ->assertSet('customers', function ($customers) {
            expect($customers)
                ->first()->name->toBe('Jane Doe')
                ->and($customers)->last()->name->toBe('John Doe');

            return true;
        })->set('sortDirection', 'desc')
        ->set('sortColumnBy', 'name')
        ->assertSet('customers', function ($customers) {
            expect($customers)
                ->first()->name->toBe('John Doe')
                ->and($customers)->last()->name->toBe('Jane Doe');

            return true;
        });
});

it('should be able to paginate the result', function () {
    $admin = User::factory()->admin()->create(['name' => 'John Doe', 'email' => 'john@globalhitss.com.br']);
    User::factory()->withPermission(Can::TESTING)->count(30)->create();

    actingAs($admin);

    Livewire::test('admin.customers.index')
        ->set('sortDirection', 'asc')
        ->set('sortColumnBy', 'name')
        ->assertSet('customers', function (LengthAwarePaginator $customers) {
            expect($customers)
                ->toHaveCount(15);

            return true;
        })->set('perPage', 20)
        ->assertSet('customers', function (LengthAwarePaginator $customers) {
            expect($customers)
                ->toHaveCount(20);

            return true;
        });
});
