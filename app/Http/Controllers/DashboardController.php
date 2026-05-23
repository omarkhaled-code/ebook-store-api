<?php

namespace App\Http\Controllers;

use App\Models\Order;

class DashboardController extends Controller
{
    // GET /api/v1/dashboard/purchases
    public function purchases()
    {
        $orders = Order::where('user_id', auth()->id())
            ->where('status', 'paid')
            ->with('ebook')
            ->latest()
            ->get()
            ->map(function ($order) {
                return [
                    'order_id'   => $order->id,
                    'paid_at'    => $order->paid_at,
                    'amount'     => $order->amount,
                    'ebook'      => [
                        'id'              => $order->ebook->id,
                        'title'           => $order->ebook->title,
                        'slug'            => $order->ebook->slug,
                        'author'          => $order->ebook->author,
                        'cover_image_path'=> $order->ebook->cover_image_path,
                    ],
                ];
            });

        return response()->json([
            'data' => $orders,
        ]);
    }
}