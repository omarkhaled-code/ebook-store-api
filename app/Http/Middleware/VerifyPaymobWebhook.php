<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyPaymobWebhook
{
    public function handle(Request $request, Closure $next)
    {
        $hmacSecret  = config('services.paymob.hmac_secret');
        $receivedHmac = $request->query('hmac');

        if (!$receivedHmac) {
            return response()->json(['error' => 'No HMAC provided.'], 403);
        }

        $data = $request->all();

        // Paymob requires these fields concatenated in this EXACT order
        $concatenated = implode('', [
            data_get($data, 'amount_cents', ''),
            data_get($data, 'created_at', ''),
            data_get($data, 'currency', ''),
            data_get($data, 'error_occured', ''),
            data_get($data, 'has_parent_transaction', ''),
            data_get($data, 'id', ''),
            data_get($data, 'integration_id', ''),
            data_get($data, 'is_3d_secure', ''),
            data_get($data, 'is_auth', ''),
            data_get($data, 'is_capture', ''),
            data_get($data, 'is_refunded', ''),
            data_get($data, 'is_standalone_payment', ''),
            data_get($data, 'is_voided', ''),
            data_get($data, 'order.id', ''),
            data_get($data, 'owner', ''),
            data_get($data, 'pending', ''),
            data_get($data, 'source_data.pan', ''),
            data_get($data, 'source_data.sub_type', ''),
            data_get($data, 'source_data.type', ''),
            data_get($data, 'success', ''),
        ]);

        // Compute HMAC using SHA512
        $computedHmac = hash_hmac('sha512', $concatenated, $hmacSecret);

        // Use hash_equals to prevent timing attacks
        if (!hash_equals($computedHmac, $receivedHmac)) {
            return response()->json(['error' => 'Invalid HMAC.'], 403);
        }

        return $next($request);
    }
}