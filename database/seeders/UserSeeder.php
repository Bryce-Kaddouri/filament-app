<?php

namespace Database\Seeders;

use App\Enums\RoleUserEnum;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 5 regular users
        User::factory()->count(5)->state([
            'role' => RoleUserEnum::ROLE_USER->name
        ])->create();

        // Create 5 admin users 
        User::factory()->count(5)->state([
            'role' => RoleUserEnum::ROLE_ADMIN->name
        ])->create();
    }
}
