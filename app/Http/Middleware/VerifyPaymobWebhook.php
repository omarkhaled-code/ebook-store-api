<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyPaymobWebhook
{
    public function handle(Request $request, Closure $next)
    {
        $hmacSecret   = config('services.paymob.hmac_secret');
        $receivedHmac = $request->query('hmac');

        

        if (!$receivedHmac) {
            return response()->json(['error' => 'No HMAC provided.'], 403);
        }


        $data = $request->input('obj');
        
        if (!$data) {
            return response()->json(['error' => 'No data provided.'], 403);
        }

        // Convert booleans to string true/false like Paymob expects
        $bool = fn($val) => $val ? 'true' : 'false';

        $concatenated = implode('', [
            data_get($data, 'amount_cents', ''),
            data_get($data, 'created_at', ''),
            data_get($data, 'currency', ''),
            $bool(data_get($data, 'error_occured', false)),
            $bool(data_get($data, 'has_parent_transaction', false)),
            data_get($data, 'id', ''),
            data_get($data, 'integration_id', ''),
            $bool(data_get($data, 'is_3d_secure', false)),
            $bool(data_get($data, 'is_auth', false)),
            $bool(data_get($data, 'is_capture', false)),
            $bool(data_get($data, 'is_refunded', false)),
            $bool(data_get($data, 'is_standalone_payment', false)),
            $bool(data_get($data, 'is_voided', false)),
            data_get($data, 'order.id', ''),
            data_get($data, 'owner', ''),
            $bool(data_get($data, 'pending', false)),
            data_get($data, 'source_data.pan', ''),
            data_get($data, 'source_data.sub_type', ''),
            data_get($data, 'source_data.type', ''),
            $bool(data_get($data, 'success', false)),
        ]);


        $computedHmac = hash_hmac('sha512', $concatenated, $hmacSecret);

       

        if (!hash_equals($computedHmac, $receivedHmac)) {
            return response()->json(['error' => 'Invalid HMAC.'], 403);
        }

        return $next($request);
    }
}
