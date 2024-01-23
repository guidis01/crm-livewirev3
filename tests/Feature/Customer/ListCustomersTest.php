<?php

use App\Livewire\Customers;
use App\Models\{Customer, User};
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, get};

it('should be able to access the route customers', function () {
    actingAs(User::factory()->create());

    get(route('customers'))
        ->assertOk();
});

it("let's create a livewire component to list all customers in the page", function () {
    actingAs(User::factory()->create());
    $customers = Customer::factory()->count(10)->create();

    $lw = Livewire::test(Customers\Index::class);

    $lw->assertSet('items', function ($items) {
        expect($items)
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
    $user = User::factory()->create();

    $john = Customer::factory()->create(['name' => 'John Doe', 'email' => 'john@globalhitss.com.br']);
    $jane = Customer::factory()->create(['name' => 'Jane Doe', 'email' => 'jani@globalhitss.com.br']);

    actingAs($user);

    Livewire::test('customers')
        ->assertSet('items', function ($items) {
            expect($items)->toHaveCount(2);

            return true;
        })
        ->set('search', 'jan')
        ->assertSet('items', function ($items) {
            expect($items)
                ->toHaveCount(1)
                ->first()->name->toBe('Jane Doe');

            return true;
        })->set('search', 'joh')
        ->assertSet('items', function ($items) {
            expect($items)
                ->toHaveCount(1)
                ->first()->name->toBe('John Doe');

            return true;
        });
});

it('should be able to sort by name', function () {
    $user = User::factory()->create();

    $john = Customer::factory()->create(['name' => 'John Doe', 'email' => 'john@globalhitss.com.br']);
    $jane = Customer::factory()->create(['name' => 'Jane Doe', 'email' => 'jani@globalhitss.com.br']);

    actingAs($user);

    Livewire::test('customers')
        ->set('sortDirection', 'asc')
        ->set('sortColumnBy', 'name')
        ->assertSet('items', function ($items) {
            expect($items)
                ->first()->name->toBe('Jane Doe')
                ->and($items)->last()->name->toBe('John Doe');

            return true;
        })->set('sortDirection', 'desc')
        ->set('sortColumnBy', 'name')
        ->assertSet('items', function ($items) {
            expect($items)
                ->first()->name->toBe('John Doe')
                ->and($items)->last()->name->toBe('Jane Doe');

            return true;
        });
});

it('should be able to paginate the result', function () {
    $user = User::factory()->create();

    Customer::factory()->count(30)->create();

    actingAs($user);

    Livewire::test('customers')
        ->set('sortDirection', 'asc')
        ->set('sortColumnBy', 'name')
        ->assertSet('items', function (LengthAwarePaginator $items) {
            expect($items)
                ->toHaveCount(15);

            return true;
        })->set('perPage', 20)
        ->assertSet('items', function (LengthAwarePaginator $items) {
            expect($items)
                ->toHaveCount(20);

            return true;
        });
});
