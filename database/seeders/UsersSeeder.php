<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()
            ->withPermission('be an admin')
            ->create([
                'name'  => 'Admin',
                'email' => 'admin@admin.com',
            ]);
    }
}
