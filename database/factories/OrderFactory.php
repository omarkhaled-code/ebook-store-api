<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Ebook;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'               => User::factory(),
            'ebook_id'              => Ebook::factory(),
            'paymob_order_id'       => null,
            'paymob_transaction_id' => null,
            'amount'                => fake()->numberBetween(10, 200),
            'status'                => 'pending',
            'paid_at'               => null,
        ];
    }

    // Helper states
    public function paid(): static
    {
        return $this->state(fn() => [
            'status'  => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn() => ['status' => 'pending']);
    }

    public function failed(): static
    {
        return $this->state(fn() => ['status' => 'failed']);
    }
}