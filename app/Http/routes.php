<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
/*
Route::get('/', function () {
    return view('welcome');
});
*/
Route::get('/', 'WelcomeController@index');

// ユーザ登録
Route::get('signup', 'Auth\AuthController@getRegister')->name('signup.get');
Route::post('signup', 'Auth\AuthController@postRegister')->name('signup.post');

// ログイン認証
Route::get('login', 'Auth\AuthController@getLogin')->name('login.get');
Route::post('login', 'Auth\AuthController@postLogin')->name('login.post');
Route::get('logout', 'Auth\AuthController@getLogout')->name('logout.get');

//ログイン認証付きのルーティング
Route::group(['middleware' => 'auth'], function () {
    Route::resource('users', 'UsersController', ['only' => ['index', 'show']]);

    //グループ内のルーティングでは、URLに/users/{id}/ が付与
    //例）上から順に
    //POST /users/{id}/follow
    //DELETE /users/{id}/unfollow
    //GET /users/{id}/followings
    //GET /users/{id}/followers
    Route::group(['prefix' => 'users/{id}'], function () { 
        Route::post('follow', 'UserFollowController@store')->name('user.follow');
        Route::delete('unfollow', 'UserFollowController@destroy')->name('user.unfollow');
        Route::get('followings', 'UsersController@followings')->name('users.followings');
        Route::get('followers', 'UsersController@followers')->name('users.followers');
        
        Route::post('favorite', 'MicropostsFavController@store')->name('user.favorite');
        Route::delete('unfavorite', 'MicropostsFavController@destroy')->name('user.unfavorite');        
        Route::get('favoritings', 'UsersController@favoritings')->name('users.favoritings');
        Route::get('favorited', 'UsersController@favorited')->name('users.favorited');
    });

    Route::resource('microposts', 'MicropostsController', ['only' => ['store', 'destroy']]);

});