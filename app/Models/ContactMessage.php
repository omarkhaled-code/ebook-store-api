<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'message',
        'is_read'
    ];


    public function markAsRead(): void
    {
        $this->is_read = true;
        $this->save();
    }
    public function markAllAsRead(): void
    {
        self::where('is_read', false)->update(['is_read' => true]);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }


}
