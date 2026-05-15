<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'token',
        'expires_at',
        'downloaded_at',
    ];

    // Cast these to Carbon date objects automatically
    protected $casts = [
        'expires_at'     => 'datetime',
        'downloaded_at'  => 'datetime',
    ];

    // Download belongs to an order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Download belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}