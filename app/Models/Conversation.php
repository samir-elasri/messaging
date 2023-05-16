<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Message;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    // use SoftDeletes;

    // protected $dates = ['deleted_at'];

    use HasFactory;

    public static function boot()
    {
        parent::boot();

        static::deleting(function (Conversation $conversation) {
            $conversation->users()->detach();
        });
    }
    
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
        ->withPivot('deleted_at', 'last_read_message_id')
        ->withTimeStamps();
    }
}
