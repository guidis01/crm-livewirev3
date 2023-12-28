<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Attributes\Rule;
use Livewire\Component;

class Delete extends Component
{
    public User $user;

    #[Rule(['required', 'confirmed'])]
    public string $confirmation = 'DART VADER';

    public ?string $confirmation_confirmation = null;

    public function render()
    {
        return view('livewire.admin.users.delete');
    }

    public function destroy(): void
    {
        $this->validate();
        $this->user->delete();

        $this->dispatch('user::deleted');
    }
}
