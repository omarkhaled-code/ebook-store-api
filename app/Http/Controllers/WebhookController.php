<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handlePaymob(Request $request)
    {
        $data = $request->all();

        // Only process successful, completed transactions
        if ($data['success'] !== 'true' || $data['pending'] === 'true') {
            return response()->json(['status' => 'ignored']);
        }

        $paymobOrderId = $data['order']['id'];

        // Find our order by Paymob's order ID
        $order = Order::where('paymob_order_id', $paymobOrderId)->first();

        if (!$order) {
            return response()->json(['status' => 'order not found'], 404);
        }

        // Idempotency check — don't process twice
        if ($order->status === 'paid') {
            return response()->json(['status' => 'already processed']);
        }

        // Mark order as paid
        $order->update([
            'status'                => 'paid',
            'paymob_transaction_id' => $data['id'],
            'paid_at'               => now(),
        ]);

        return response()->json(['status' => 'ok']);
    }
}