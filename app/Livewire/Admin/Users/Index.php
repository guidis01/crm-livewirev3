<?php

namespace App\Livewire\Admin\Users;

use App\Enum\Can;
use App\Models\{Permission, User};
use Illuminate\Database\Eloquent\{Builder, Collection};
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\{Component, WithPagination};

/**
 * @property-read Collection|User[] $users
 * @property-read array $headers
 */
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public ?string $search = null;

    public array $search_permissions = [];

    public bool $search_trash = false;

    public Collection $permissionsToSearch;

    public string $sortDirection = 'asc';

    public string $sortColumnBy = 'id';

    public int $perPage = 15;

    public function mount(): void
    {
        $this->authorize(Can::BE_AN_ADMIN->value);
        $this->filterPermissions();
    }

    public function updatedPerPage($value): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.admin.users.index');
    }

    #[Computed]
    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->with('permissions')
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
            ->orderBy($this->sortColumnBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    #[Computed]
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'sortColumnBy' => $this->sortColumnBy   , 'sortDirection' => $this->sortDirection],
            ['key' => 'name', 'label' => 'Name', 'sortColumnBy' => $this->sortColumnBy  , 'sortDirection' => $this->sortDirection],
            ['key' => 'email', 'label' => 'Email', 'sortColumnBy' => $this->sortColumnBy, 'sortDirection' => $this->sortDirection],
            ['key' => 'permissions', 'label' => 'Permissions', 'sortColumnBy' => $this->sortColumnBy, 'sortDirection' => $this->sortDirection],
        ];
    }

    public function filterPermissions(?string $value = null): void
    {
        $this->permissionsToSearch = Permission::query()
           ->when($value, fn (Builder $q) => $q->where('key', 'like', "%$value%"))
           ->orderBy('key')
           ->get();
    }

    public function sortBy(string $column, string $direction): void
    {
        $this->sortColumnBy  = $column;
        $this->sortDirection = $direction;
    }
}
