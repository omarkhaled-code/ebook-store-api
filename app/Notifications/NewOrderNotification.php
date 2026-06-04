<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    // Store in database only
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    // What data to store
    public function toDatabase(object $notifiable): array
    {
        return [
            'message'    => "New purchase: {$this->order->ebook->title}",
            'user_name'  => $this->order->user->name,
            'user_email' => $this->order->user->email,
            'ebook_title'=> $this->order->ebook->title,
            'amount'     => $this->order->amount,
            'order_id'   => $this->order->id,
        ];
    }
}