<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymobService;
use App\Http\Requests\InitiatePaymentRequest;

class PaymentController extends Controller
{
    private PaymobService $paymobService;

    // Laravel automatically injects PaymobService here
    public function __construct(PaymobService $paymobService)
    {
        $this->paymobService = $paymobService;
    }

    public function initiate(InitiatePaymentRequest $request)
    {
        // Get the order — make sure it belongs to this user
        $order = Order::where('id', $request->order_id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->with('ebook')
            ->firstOrFail();

        $user  = auth()->user();

        // Billing data required by Paymob
        $billingData = [
            'apartment'     => 'NA',
            'email'         => $user->email,
            'floor'         => 'NA',
            'first_name'    => $user->name,
            'street'        => 'NA',
            'building'      => 'NA',
            'phone_number'  => 'NA',
            'shipping_method' => 'NA',
            'postal_code'   => 'NA',
            'city'          => 'NA',
            'country'       => 'EG',
            'last_name'     => 'NA',
            'state'         => 'NA',
        ];

        try {
            // Step 1 — Get Paymob auth token
            $authToken = $this->paymobService->getAuthToken();

            // Step 2 — Create Paymob order
            $paymobOrder = $this->paymobService->createOrder(
                $authToken,
                $order->ebook->price_in_cents,
                $order->ebook->title
            );

            // Step 3 — Get payment key
            $paymentKey = $this->paymobService->getPaymentKey(
                $authToken,
                $paymobOrder['id'],
                $order->ebook->price_in_cents,
                $billingData
            );

            // Save Paymob order ID to our order
            $order->update([
                'paymob_order_id' => $paymobOrder['id'],
            ]);

            return response()->json([
                'payment_key' => $paymentKey,
                'iframe_url'  => $this->paymobService->getIframeUrl($paymentKey),
                'order_id'    => $order->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Payment initiation failed. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}