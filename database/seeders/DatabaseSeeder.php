<?php

namespace Database\Seeders;

use App\Models\Ebook;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin Account',
            'email' => 'admin@example.com',
            'password' => bcrypt('passpass'), // Use a secure password in production
            "role" => 'admin',
        ]);

        User::factory()->create([
            'name' => 'User Account',
            'email' => 'user@example.com',
            'password' => bcrypt('passpass'), // Use a secure password in production
            "role" => 'user',
        ]);

        Ebook::factory()->count(30)->create();
    }
}
