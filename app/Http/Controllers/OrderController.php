<?php

namespace App\Http\Controllers;

use App\Models\Ebook;
use App\Models\Order;
use App\Http\Requests\StoreOrderRequest;

class OrderController extends Controller
{
    // POST /api/v1/orders — create a pending order
    public function store(StoreOrderRequest $request)
    {
        $ebook = Ebook::findOrFail($request->ebook_id);

        // Check if user already purchased this ebook
        $existingOrder = Order::where('user_id', auth()->id())
            ->where('ebook_id', $ebook->id)
            ->where('status', 'paid')
            ->first();

        if ($existingOrder) {
            return response()->json([
                'message' => 'You already purchased this ebook.',
            ], 409);
        }

        // Check ebook is published
        if (!$ebook->is_published) {
            return response()->json([
                'message' => 'This ebook is not available.',
            ], 404);
        }

        // Create pending order
        $order = Order::create([
            'user_id'  => auth()->id(),
            'ebook_id' => $ebook->id,
            'amount'   => $ebook->price,
            'status'   => 'pending',
        ]);

        return response()->json([
            'message' => 'Order created successfully.',
            'data'    => [
                'order_id'  => $order->id,
                'amount'    => $order->amount,
                'status'    => $order->status,
                'ebook'     => [
                    'id'    => $ebook->id,
                    'title' => $ebook->title,
                    'price' => $ebook->price,
                ],
            ],
        ], 201);
    }

    // GET /api/v1/orders/{id} — check order status
    public function show(int $id)
    {
        $order = Order::where('id', $id)
            ->where('user_id', auth()->id())
            ->with('ebook')
            ->firstOrFail();

        return response()->json([
            'data' => [
                'id'      => $order->id,
                'status'  => $order->status,
                'amount'  => $order->amount,
                'paid_at' => $order->paid_at,
                'ebook'   => [
                    'id'    => $order->ebook->id,
                    'title' => $order->ebook->title,
                    'slug'  => $order->ebook->slug,
                ],
            ],
        ]);
    }
}