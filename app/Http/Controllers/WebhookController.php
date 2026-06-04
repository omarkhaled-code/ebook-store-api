<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handlePaymob(Request $request)
    {
        // الحصول على البيانات المرسلة
        $data = $request->all();

        // البيانات الأساسية توجد داخل مفتاح 'obj'
        $obj = $data['obj'] ?? null;

        if (!$obj) {
            return response()->json(['status' => 'invalid data'], 400);
        }

        // الآن نتحقق من success و pending من داخل الـ obj
        // ملاحظة: Paymob ترسل success كقيمة منطقية (boolean) وليس نص (string)
        if ($obj['success'] !== true || $obj['pending'] === true) {
            return response()->json(['status' => 'ignored']);
        }

        $paymobOrderId = $obj['order']['id'];

        // Find our order by Paymob's order ID
        $order = Order::where('paymob_order_id', $paymobOrderId)->first();

        if (!$order) {
            return response()->json(['status' => 'order not found'], 404);
        }

        // Idempotency check
        if ($order->status === 'paid') {
            return response()->json(['status' => 'already processed']);
        }

        // Mark order as paid
        Log::info("OBJ: " . json_encode($obj)); // تسجيل البيانات للتأكد من البنية  
        $order->update([
            'status'                => 'paid',
            'paymob_transaction_id' => $obj['id'], // استخدم $obj['id'] بدلاً من $data['id']
            'paid_at'               => now(),
        ]);

        return response()->json(['status' => 'ok']);
    }
}
