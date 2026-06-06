<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status?->value ?? $this->status,
            'amount' => $this->amount,
            'paid_at' => $this->paid_at,
            'paymob_order_id' => $this->when($request->user()?->isAdmin(), $this->paymob_order_id),
            'paymob_transaction_id' => $this->when($request->user()?->isAdmin(), $this->paymob_transaction_id),
            'user' => UserResource::make($this->whenLoaded('user')),
            'ebook' => EbookResource::make($this->whenLoaded('ebook')),
            'created_at' => $this->created_at,
        ];
    }
}
