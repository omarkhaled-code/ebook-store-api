<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // Only create admin if doesn't exist — safe to run multiple times
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@ebookstore.com')],
            [
                'name'              => 'Admin',
                'password'          => Hash::make(env('ADMIN_PASSWORD', 'changeme123')),
                'role'              => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin account created successfully!');
    }
}