<?php

use App\Livewire\Auth\Logout;

it('should be able to logout off the application', function () {
    $user = \App\Models\User::factory()->create();

    \Pest\Laravel\actingAs($user);

    \Livewire\Livewire::test(Logout::class)
        ->call('logout')
        ->assertRedirect(route('login'));

    expect(auth())
        ->guest()
        ->toBeTrue();
});
