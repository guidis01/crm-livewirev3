<?php

use App\Models\User;

use function Pest\Laravel\{actingAs, get};

//it('should show the current branch in the page', function () {
//    Process::fake([
//        'git branch --show-current' => Process::result('luamaria'),
//    ]);
//
//    Livewire::test(BranchEnv::class)
//        ->assertSet('branch', 'luamaria')
//        ->assertSee('luamaria');
//
//    Process::assertRan('git branch --show-current');
//});

it('should not load the livewire component on production environment', function () {
    $user = User::factory()->create();

    app()->detectEnvironment(fn () => 'production');

    actingAs($user);

    get(route('dashboard'))
        ->assertDontSeeLivewire('dev.branch-env');

    get(route('login'))
        ->assertDontSeeLivewire('dev.branch-env');
});

it('should load the livewire component on non production environments', function () {
    $user = User::factory()->create();

    app()->detectEnvironment(fn () => 'local');

    actingAs($user);

    get(route('dashboard'))
        ->assertSeeLivewire('dev.branch-env');

    get(route('login'))
        ->assertSeeLivewire('dev.branch-env');
});
