<?php

use App\Livewire\Auth\Register;
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
    ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'name'  => 'John Doe',
        'email' => 'john@doe.com',
    ]);

    $this->assertDatabaseCount('users', 1);
});
