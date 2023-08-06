<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable=[
        'chat_room_id',
        'user_id',
        'message',
    ];

    public function chat_room(){
        return $this->belongsTo(ChatRoom::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
