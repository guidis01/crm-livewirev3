<?php

namespace App\Livewire\Admin\Users;

use App\Enum\Can;
use App\Models\{Permission, User};
use Illuminate\Database\Eloquent\{Builder, Collection};
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property-read Collection|User[] $users
 * @property-read array $headers
 */
class Index extends Component
{
    public ?string $search = null;

    public array $search_permissions = [];

    public bool $search_trash = false;

    public Collection $permissionsToSearch;

    public function mount(): void
    {
        $this->authorize(Can::BE_AN_ADMIN->value);
        $this->filterPermissions();
    }

    public function render(): View
    {
        return view('livewire.admin.users.index');
    }

    #[Computed]
    public function users(): Collection
    {
        return User::query()
            ->when(
                $this->search,
                fn (Builder $q) => $q
                    ->where(
                        DB::raw('lower(name)'),
                        'like',
                        '%' . strtolower($this->search) . '%'
                    )
                    ->orWhere(
                        'email',
                        'like',
                        '%' . strtolower($this->search) . '%'
                    )
            )->when(
                $this->search_permissions,
                fn (Builder $q) => $q
                    ->whereHas('permissions', function ($query) {
                        $query->whereIn('id', $this->search_permissions);
                    })
            )->when(
                $this->search_trash,
                fn (Builder $q) => $q->onlyTrashed() /** @phpstan-ignore-line*/
            )
            ->get();
    }

    #[Computed]
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'permissions', 'label' => 'Permissions'],
        ];
    }

    public function filterPermissions(?string $value = null): void
    {
        $this->permissionsToSearch = Permission::query()
           ->when($value, fn (Builder $q) => $q->where('key', 'like', "%$value%"))
           ->orderBy('key')
           ->get();
    }
}
