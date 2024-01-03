<?php

use App\Livewire\Admin;
use App\Models\User;
use App\Notifications\UserRestoredNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, assertNotSoftDeleted, assertSoftDeleted};

it('should be able to restore a user', function () {
    $user            = User::factory()->admin()->create();
    $forRestauration = User::factory()->deleted()->create();

    actingAs($user);

    Livewire::test(Admin\Users\Restore::class)
        ->set('user', $forRestauration)
        ->set('confirmation_confirmation', 'YODA')
        ->call('restore')
        ->assertDispatched('user::restored');

    assertNotSoftDeleted('users', [
        'id' => $forRestauration->id,
    ]);

    $forRestauration->refresh();

    expect($forRestauration)->restored_at->not->toBeNull()->restoredBy->id->toBe($user->id);

});

it('should have a confirmation before deletion', function () {
    $user            = User::factory()->admin()->create();
    $forRestauration = User::factory()->deleted()->create();

    actingAs($user);

    Livewire::test(Admin\Users\Restore::class)
        ->set('user', $forRestauration)
        ->call('restore')
        ->assertHasErrors(['confirmation' => 'confirmed'])
        ->assertNotDispatched('user::restored');

    assertSoftDeleted('users', [
        'id' => $forRestauration->id,
    ]);
});

it('should send a notification to the user telling him that he has again access to the application', function () {
    Notification::fake();
    $user            = User::factory()->admin()->create();
    $forRestauration = User::factory()->deleted()->create();

    actingAs($user);

    Livewire::test(Admin\Users\Restore::class)
        ->set('user', $forRestauration)
        ->set('confirmation_confirmation', 'YODA')
        ->call('restore');
    Notification::assertSentTo($forRestauration, UserRestoredNotification::class);
});
