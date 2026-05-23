<?php

namespace App\Services;

use App\Models\Download;
use App\Models\Order;
use Illuminate\Support\Str;

class DownloadService
{
    // Generate a secure download token for a paid order
    public function generateToken(Order $order, int $userId): Download
    {
        // Make sure order is paid
        if ($order->status !== 'paid') {
            throw new \Exception('Order is not paid.');
        }

        // Make sure this order belongs to this user
        if ($order->user_id !== $userId) {
            throw new \Exception('Unauthorized.');
        }

        // Delete any existing token for this order
        Download::where('order_id', $order->id)->delete();

        // Create a fresh token
        return Download::create([
            'order_id'    => $order->id,
            'user_id'     => $userId,
            'token'       => Str::random(64),
            'expires_at'  => now()->addHours(24),
        ]);
    }
}