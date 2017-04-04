<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];
    
    //1対多
    public function microposts()
    {
        return $this->hasMany(Micropost::class);
    }

    //フォロー機能（多対多）
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }
    
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
    }
    
    public function follow($userId)
    {
        //すでにフォローしているかの確認
        $exist = $this->is_following($userId);
        //自分自身ではないかの確認
        $its_me = $this->id == $userId;
        
        if($exist || $its_me){
            //すでにフォローしていれば何もしない
            return false;
        } else {
            //未フォローであればフォローする
            $this->followings()->attach($userId);
            return true;
        }
    }
    
    public function unfollow($userId)
    {
        //すでにフォローしているかの確認
        $exist = $this->is_following($userId);
        //自分自身でないかの確認
        $its_me = $this->id == $userId;
        
        if($exist || $its_me){
            //すでにフォローしていればフォローを外す
            $this->followings()->detach($userId);
            return true;
        } else {
            //未フォローであれば何もしない
            return false;
        }
    }
    
    public function is_following($userId){
        return $this->followings()->where('follow_id', $userId)->exists();
    }

    //お気に入り機能（多対多）
    public function favoritings()
    {
        return $this->belongsToMany(Micropost::class, 'favorite', 'user_id', 'favorite_id')->withTimestamps();
    }    

    public function favorite($micropostId)
    {
        // 既にお気に入りしているかの確認
        $exist = $this->is_favorite($micropostId);

        if ($exist) {
            // 既にお気に入りしていれば何もしない
            return false;
        } else {
            // 未フォローであればお気に入りする
            $this->favoritings()->attach($micropostId);
            return true;
        }
    }

    public function unfavorite($micropostId)
    {
        // 既にお気に入りしているかの確認
        $exist = $this->is_favorite($micropostId);

        if ($exist) {
            // 既にお気に入りしていればお気に入りを外す
            $this->favoritings()->detach($micropostId);
            return true;
        } else {
            // 未お気に入りであれば何もしない
            return false;
        }
    }   

    public function is_favorite($micropostId) {
        return $this->favoritings()->where('favorite_id', $micropostId)->exists();
    }

    //タイムライン用
    public function feed_microposts()
    {
        $follow_user_ids = $this->followings()->lists('users.id')->toArray();
        $follow_user_ids[] = $this->id;
        return Micropost::whereIn('user_id', $follow_user_ids);
    }
    public function feed_favorites()
    {
        $favorite_user_ids = $this->favoritings()->lists('microposts.id')->toArray();
        return Micropost::whereIn('favorite_id', $favorite_user_ids);
    }
    
}
