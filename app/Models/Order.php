<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ebook_id',
        'paymob_order_id',
        'paymob_transaction_id',
        'amount',
        'status',
        'paid_at',
    ];

    // Cast paid_at to a Carbon date object automatically
    protected $casts = [
        'paid_at' => 'datetime',
    ];

    // Order belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Order belongs to an ebook
    public function ebook()
    {
        return $this->belongsTo(Ebook::class);
    }

    // Order has one download token
    public function download()
    {
        return $this->hasOne(Download::class);
    }
}
