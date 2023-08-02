<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'user_id',
        'author_id',
        'publish_at',
        'privacy'
    ];

    public function Tags()
    {
        return $this->hasMany(PostTags::class);
    }

    public function Images()
    {
        return $this->hasMany(PostImages::class);
    }
    public function Comments()
    {
        return $this->hasMany(PostComments::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function Reacts()
    {
        return $this->hasMany(PostReacts::class);
    }

    public function Author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
