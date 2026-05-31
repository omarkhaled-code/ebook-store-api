<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class AdminOrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'ebook'])
            ->latest()
            ->paginate(20);

        return response()->json($orders);
    }
}