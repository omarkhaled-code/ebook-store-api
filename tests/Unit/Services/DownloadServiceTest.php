<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\DownloadService;
use App\Models\User;
use App\Models\Ebook;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test; 

class DownloadServiceTest extends TestCase
{
    use RefreshDatabase;

    private DownloadService $downloadService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->downloadService = new DownloadService();
    }

    #[Test]
    public function it_generates_download_token_for_paid_order(): void
    {
        $user  = User::factory()->create();
        $ebook = Ebook::factory()->create();
        $order = Order::factory()->create([
            'user_id'  => $user->id,
            'ebook_id' => $ebook->id,
            'status'   => 'paid',
        ]);

        $download = $this->downloadService->generateToken($order, $user->id);

        $this->assertNotNull($download->token);
        $this->assertEquals(64, strlen($download->token));
        $this->assertEquals($order->id, $download->order_id);
        $this->assertEquals($user->id, $download->user_id);
        $this->assertTrue($download->expires_at->isFuture());
    }

    #[Test]
    public function it_throws_exception_for_unpaid_order(): void
    {
        $user  = User::factory()->create();
        $ebook = Ebook::factory()->create();
        $order = Order::factory()->create([
            'user_id'  => $user->id,
            'ebook_id' => $ebook->id,
            'status'   => 'pending',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Order is not paid.');

        $this->downloadService->generateToken($order, $user->id);
    }

    #[Test]
    public function it_throws_exception_when_user_does_not_own_order(): void
    {
        $user      = User::factory()->create();
        $otherUser = User::factory()->create();
        $ebook     = Ebook::factory()->create();
        $order     = Order::factory()->create([
            'user_id'  => $user->id,
            'ebook_id' => $ebook->id,
            'status'   => 'paid',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unauthorized.');

        $this->downloadService->generateToken($order, $otherUser->id);
    }

    #[Test]
    public function it_deletes_old_token_before_creating_new_one(): void
    {
        $user  = User::factory()->create();
        $ebook = Ebook::factory()->create();
        $order = Order::factory()->create([
            'user_id'  => $user->id,
            'ebook_id' => $ebook->id,
            'status'   => 'paid',
        ]);

        $first  = $this->downloadService->generateToken($order, $user->id);
        $second = $this->downloadService->generateToken($order, $user->id);

        $this->assertDatabaseMissing('downloads', ['token' => $first->token]);
        $this->assertDatabaseHas('downloads', ['token' => $second->token]);
    }
}