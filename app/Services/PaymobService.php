<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PaymobService
{
    private string $apiKey;
    private string $integrationId;
    private string $iframeId;

    public function __construct()
    {
        $this->apiKey        = config('services.paymob.api_key');
        $this->integrationId = config('services.paymob.integration_id');
        $this->iframeId      = config('services.paymob.iframe_id');
    }

    // Step 1 — Get auth token from Paymob
    public function getAuthToken(): string
    {
        $response = Http::post('https://accept.paymob.com/api/auth/tokens', [
            'api_key' => $this->apiKey,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Paymob authentication failed.');
        }

        return $response->json('token');
    }

    // Step 2 — Create order on Paymob
    public function createOrder(string $authToken, int $amountCents, string $ebookTitle): array
    {
        $response = Http::post('https://accept.paymob.com/api/ecommerce/orders', [
            'auth_token'       => $authToken,
            'delivery_needed'  => false,
            'amount_cents'     => $amountCents,
            'currency'         => 'EGP',
            'items'            => [
                [
                    'name'        => $ebookTitle,
                    'amount_cents'=> $amountCents,
                    'description' => 'PDF Ebook',
                    'quantity'    => 1,
                ],
            ],
        ]);

        if (!$response->successful()) {
            throw new \Exception('Paymob order creation failed.');
        }

        return $response->json();
    }

    // Step 3 — Get payment key
    public function getPaymentKey(
        string $authToken,
        int $paymobOrderId,
        int $amountCents,
        array $billingData
    ): string {
        $response = Http::post('https://accept.paymob.com/api/acceptance/payment_keys', [
            'auth_token'     => $authToken,
            'amount_cents'   => $amountCents,
            'expiration'     => 3600,
            'order_id'       => $paymobOrderId,
            'billing_data'   => $billingData,
            'currency'       => 'EGP',
            'integration_id' => $this->integrationId,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Paymob payment key generation failed.');
        }

        return $response->json('token');
    }

    // Build the iframe URL from payment key
    public function getIframeUrl(string $paymentKey): string
    {
        return "https://accept.paymob.com/api/acceptance/iframes/{$this->iframeId}?payment_token={$paymentKey}";
    }
}