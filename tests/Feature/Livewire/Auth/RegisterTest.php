<?php

use App\Livewire\Auth\Register;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Livewire\Livewire;

it('should render the component', function () {
    Livewire::test(Register::class)
    ->assertStatus(200);
});

it('should be able to register a new user in the system', function () {
    Livewire::test(Register::class)
    ->set('name', 'John Doe')
    ->set('email', 'john@doe.com')
    ->set('email_confirmation', 'john@doe.com')
    ->set('password', 'password')
    ->call('submit')
    ->assertHasNoErrors()
    ->assertRedirect(RouteServiceProvider::HOME);

    $this->assertDatabaseHas('users', [
        'name'  => 'John Doe',
        'email' => 'john@doe.com',
    ]);

    $this->assertDatabaseCount('users', 1);

    expect(auth()->check())
        ->and(auth()->user())
        ->id->toBe(User::first()->id);
});

test('validation rules', function ($f) {
    if($f->rule == 'unique') {
        User::factory()->create([$f->field => $f->value]);
    }

    $livewire = Livewire::test(Register::class)
        ->set($f->field, $f->value);

    if (property_exists($f, 'aValue')) {
        $livewire->set($f->aField, $f->aValue);
    }

    $livewire->call('submit')
        ->assertHasErrors([$f->field => $f->rule]);
})->with([
    'name::required'     => (object)['field' => 'name', 'value' => '', 'rule' => 'required'],
    'name::max:255'      => (object)['field' => 'name', 'value' => str_repeat('*', 256), 'rule' => 'max'],
    'email::required'    => (object)['field' => 'email', 'value' => '', 'rule' => 'required'],
    'email::email'       => (object)['field' => 'email', 'value' => 'not-an-email', 'rule' => 'email'],
    'email::max:255'     => (object)['field' => 'email', 'value' => str_repeat('*' . '@doe.com', 256), 'rule' => 'max'],
    'email::confirmed'   => (object)['field' => 'email', 'value' => 'john@doe.com', 'rule' => 'confirmed'],
    'email::unique'      => (object)['field' => 'email', 'value' => 'john@doe.com', 'rule' => 'unique', 'aField' => 'email_confirmation', 'aValue' => 'john@doe.com'],
    'password::required' => (object)['field' => 'password', 'value' => '', 'rule' => 'required'],
]);
