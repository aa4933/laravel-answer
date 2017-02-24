<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
function rq($key = null, $default = null)
{
    if (!$key)
        return Request::all();
    return Request::get($key, $default);
}

function user_ins()
{
    $user = new \App\User();
    return $user;
}

function question_ins()
{
    $question = new \App\Question();
    return $question;
}

Route::get('/', function () {
    return view('welcome');
});
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
    Route::any('test', function () {dd(user_ins()->is_login());});


    Route::any('api', function () {return ['version' => '0.1'];});

    //用户api
    Route::any('api/signup', function () {return user_ins()->signup();});
    Route::any('api/login', function () {return user_ins()->login();});
    Route::any('api/logout', function () {return user_ins()->logout();});
    //问题api
    Route::any('api/question/add', function () {return question_ins()->add();});
    Route::any('api/question/change', function () {return question_ins()->change();});
    Route::any('api/question/look', function () {return question_ins()->look();});
});
