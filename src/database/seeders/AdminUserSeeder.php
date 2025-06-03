<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminEmail = "admin@sonarsailor.com";
        $adminPassword = "12345678";
    
        User::updateOrCreate(
            ['email' => $adminEmail], // Ensure email uniqueness
            [
                'name' => 'Admin',
                'password' => Hash::make($adminPassword),
                'is_admin' => true,
            ]
        );
    }
    
}
