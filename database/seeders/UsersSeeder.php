<?php

namespace Database\Seeders;

use App\Enum\Can;
use App\Models\{User};
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()
             ->withPermission(Can::BE_AN_ADMIN)
             ->create([
                 'name'  => 'Admin do CRM',
                 'email' => 'admin@admin.com',
             ]);

        User::factory()->count(50)->create();
        User::factory()->count(50)->deleted()->create();

        $user = User::find(3);
        $user->givePermissionTo(Can::BE_AN_ADMIN);
    }
}
