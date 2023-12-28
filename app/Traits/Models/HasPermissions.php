<?php

namespace App\Traits\Models;

use App\Models\{App\Enum\Can, Permission};
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

trait HasPermissions
{
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function givePermissionTo(\App\Enum\Can|string $key): void
    {
        $pKey = $key instanceof \App\Enum\Can ? $key->value : $key;

        $this->permissions()->firstOrCreate(['key' => $pKey]);

        Cache::forget($this->getPermissionCacheKey());
        Cache::rememberForever(
            $this->getPermissionCacheKey(),
            fn () => $this->permissions
        );
    }

    public function hasPermissionTo(\App\Enum\Can|string $key): bool
    {
        $pKey = $key instanceof \App\Enum\Can ? $key->value : $key;

        /** var Collection $permissions */
        $permissions = Cache::get($this->getPermissionCacheKey(), $this->permissions);

        return $permissions
            ->where('key', '=', $pKey)
            ->isNotEmpty();
    }

    /**
     * @return string
     * */
    private function getPermissionCacheKey(): string
    {
        return "user::{$this->id}::permissions";
    }
}
