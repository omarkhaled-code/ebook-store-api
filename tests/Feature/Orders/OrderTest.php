<?php

namespace Tests\Feature\Orders;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ebook;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function authenticated_user_can_create_order(): void
    {
        $user  = User::factory()->create(['email_verified_at' => now()]);
        $ebook = Ebook::factory()->create(['is_published' => true]);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/orders', [
                'ebook_id' => $ebook->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['order_id', 'amount', 'status'],
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id'  => $user->id,
            'ebook_id' => $ebook->id,
            'status'   => 'pending',
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_create_order(): void
    {
        $ebook = Ebook::factory()->create();

        $response = $this->postJson('/api/v1/orders', [
            'ebook_id' => $ebook->id,
        ]);

        $response->assertStatus(401);
    }

    #[Test]
    public function user_cannot_buy_same_ebook_twice(): void
    {
        $user  = User::factory()->create(['email_verified_at' => now()]);
        $ebook = Ebook::factory()->create(['is_published' => true]);

        // Create paid order
        Order::factory()->create([
            'user_id'  => $user->id,
            'ebook_id' => $ebook->id,
            'status'   => 'paid',
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/orders', [
                'ebook_id' => $ebook->id,
            ]);

        $response->assertStatus(409)
            ->assertJson(['message' => 'You already purchased this ebook.']);
    }

    #[Test]
    public function unverified_user_cannot_purchase(): void
    {
        $user  = User::factory()->create(['email_verified_at' => null]);
        $ebook = Ebook::factory()->create(['is_published' => true]);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/orders', [
                'ebook_id' => $ebook->id,
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function user_cannot_view_another_users_order(): void
    {
        $user      = User::factory()->create();
        $otherUser = User::factory()->create();
        $ebook     = Ebook::factory()->create();

        $order = Order::factory()->create([
            'user_id'  => $otherUser->id,
            'ebook_id' => $ebook->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(404);
    }
}