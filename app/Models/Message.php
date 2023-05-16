<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Converstaion;


class Message extends Model
{
    use HasFactory;

    protected $fillable = ['content', 'image'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function markAsRead(Message $message)
    {
        $message->is_read = true;
        $message->save();
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function photo(){
        $path = ($this->conversation->path).'/'.($this->photo);
        return $path;
    }
}
