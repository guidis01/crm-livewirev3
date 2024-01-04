<?php

namespace App\Livewire\Auth;

use App\Notifications\ValidationCodeNotification;
use Illuminate\View\View;
use Livewire\Component;

class EmailValidation extends Component
{
    public ?string $code = null;

    public function render(): View
    {
        return view('livewire.auth.email-validation');
    }

    public function handle(): void
    {
        $this->validate([
            'code' => function (string $attribute, mixed $value, \Closure $fail) {
                if ($value !== auth()->user()->validation_code) {
                    $fail("Invalid code");
                }
            },
        ]);
    }

    public function sendNewCode(): void
    {
        $user                  = auth()->user();
        $user->validation_code = random_int(100000, 999999);
        $user->save();
        $user->notify(new ValidationCodeNotification());
    }
}
