<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PaymobService;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;  

class PaymobServiceTest extends TestCase
{
    private PaymobService $paymobService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymobService = new PaymobService();
    }

    #[Test]  // 👈 replace /** @test */
    public function it_gets_auth_token_from_paymob(): void
    {
        Http::fake([
            'accept.paymob.com/api/auth/tokens' => Http::response([
                'token' => 'fake_auth_token_123'
            ], 200)
        ]);

        $token = $this->paymobService->getAuthToken();

        $this->assertEquals('fake_auth_token_123', $token);
    }

    #[Test]
    public function it_throws_exception_when_paymob_auth_fails(): void
    {
        Http::fake([
            'accept.paymob.com/api/auth/tokens' => Http::response([], 500)
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Paymob authentication failed.');

        $this->paymobService->getAuthToken();
    }

    #[Test]
    public function it_creates_paymob_order(): void
    {
        Http::fake([
            'accept.paymob.com/api/ecommerce/orders' => Http::response([
                'id'           => 123456,
                'amount_cents' => 14999,
            ], 200)
        ]);

        $order = $this->paymobService->createOrder(
            'fake_token',
            14999,
            'Mastering Laravel'
        );

        $this->assertEquals(123456, $order['id']);
        $this->assertEquals(14999, $order['amount_cents']);
    }

    #[Test]
    public function it_generates_correct_iframe_url(): void
    {
        $paymentKey = 'test_payment_key_abc123';
        $url        = $this->paymobService->getIframeUrl($paymentKey);

        $this->assertStringContainsString($paymentKey, $url);
        $this->assertStringContainsString('accept.paymob.com', $url);
    }
}