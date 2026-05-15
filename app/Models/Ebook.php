<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ebook extends Model
{
    use HasFactory;

    // These fields can be filled via create() or update()
    protected $fillable = [
        'title',
        'slug',
        'description',
        'author',
        'cover_image_path',
        'pdf_path',
        'price',
        'price_in_cents',
        'is_published',
    ];

    // One ebook has many orders
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}