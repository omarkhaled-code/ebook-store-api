<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminNotificationController extends Controller
{
    // GET /api/v1/admin/notifications
    public function index()
    {
        $user = auth()->user();

        return response()->json([
            'notifications' => $user->notifications()->latest()->take(20)->get()->map(fn($n) => [
                'id'        => $n->id,
                'data'      => $n->data,
                'read'      => !is_null($n->read_at),
                'created_at'=> $n->created_at,
            ]),
            'unread_count'  => $user->unreadNotifications()->count(),
        ]);
    }

    // PATCH /api/v1/admin/notifications/{id}/read
    public function markRead(string $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['message' => 'Marked as read.']);
    }

    // PATCH /api/v1/admin/notifications/read-all
    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All marked as read.']);
    }
}