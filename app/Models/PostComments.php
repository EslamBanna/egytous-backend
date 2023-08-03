<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostComments extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'comment',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function commentImages()
    {
        return $this->hasOne(CommentImage::class, 'comment_id');
    }

    public function getCreatedAtAttribute($date)
{
    // return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d');
    return Carbon::parse($date)->format('Y-m-d');
}
}
