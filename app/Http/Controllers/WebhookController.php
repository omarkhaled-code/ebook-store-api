<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handlePaymob(Request $request)
    {

        $data = $request->input('obj'); // 👈 get the obj key

        if (!$data) {
            return response()->json(['status' => 'no data'], 400);
        }

        // Only process successful, completed transactions
        if ($data['success'] !== true || $data['pending'] === true) {
            return response()->json(['status' => 'ignored']);
        }

        $paymobOrderId = $data['order']['id'];

        $order = Order::where('paymob_order_id', $paymobOrderId)->first();

        if (!$order) {
            // \Log::error('Order not found for paymob_order_id: ' . $paymobOrderId);
            return response()->json(['status' => 'order not found'], 404);
        }

        if ($order->status === 'paid') {
            return response()->json(['status' => 'already processed']);
        }
      
        $order->update([
            'status'                => 'paid',
            'paymob_transaction_id' => $data['id'],
            'paid_at'               => now(),
        ]);

        // 👈 Notify all admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewOrderNotification($order->load(['user', 'ebook'])));
        }

        return response()->json(['status' => 'ok']);
    }
}
