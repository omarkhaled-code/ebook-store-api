<?php

namespace Database\Seeders;

use App\Models\Ebook;
use App\Models\Order;
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

        User::factory()->create([
            'name' => 'Admin Account',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            "role" => 'admin',
        ]);

        User::factory()->create([
            'name' => 'User Account',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            "role" => 'user',
        ]);

        Ebook::factory()->count(30)->create();
        Order::factory()->count(100)->create();
    }
}
