<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Micropost extends Model
{
    //
    protected $fillable = ['content', 'user_id', 'favorite_id'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //お気に入り機能（多対多）
    public function favorited()
    {
        return $this->belongsToMany(User::class, 'favorite', 'favorite_id', 'user_id')->withTimestamps();
    } 

}
