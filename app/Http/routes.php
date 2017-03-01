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
function paginate($page=1, $limit=15)
{
    $limit=$limit?:16;
    $skip = $limit * (($page ?: 1) - 1);
    return [$limit, $skip];
}

function rq($key = null, $default = null)
{
    if (!$key)
        return Request::all();
    return Request::get($key, $default);
}

function err($msg=null){
    return
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

function answer_ins()
{
    $answer = new \App\Answer();
    return $answer;
}

function comment_ins()
{
    $comment = new \App\Comment();
    return $comment;
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
    Route::any('test', function () {
        dd(user_ins()->is_login());
    });


    Route::any('api', function () {
        return ['version' => '0.1'];
    });

    //用户API
    Route::any('api/signup', function () {
        return user_ins()->signup();
    });
    Route::any('api/login', function () {
        return user_ins()->login();
    });
    Route::any('api/logout', function () {
        return user_ins()->logout();
    });
    //问题API
    Route::any('api/question/add', function () {
        return question_ins()->add();
    });
    Route::any('api/question/change', function () {
        return question_ins()->change();
    });
    Route::any('api/question/look', function () {
        return question_ins()->look();
    });
    Route::any('api/question/del', function () {
        return question_ins()->del();
    });
    //回答API
    Route::any('api/answer/add', function () {
        return answer_ins()->add();
    });
    Route::any('api/answer/change', function () {
        return answer_ins()->change();
    });
    Route::any('api/answer/look', function () {
        return answer_ins()->look();
    });
    Route::any('api/answer/del', function () {
        return answer_ins()->del();
    });
    Route::any('api/answer/vote', function () {
        return answer_ins()->vote();
    });
    //评论API
    Route::any('api/comment/add', function () {
        return comment_ins()->add();
    });
    Route::any('api/comment/look', function () {
        return comment_ins()->look();
    });
    Route::any('api/comment/del', function () {
        return comment_ins()->del();
    });


    //通用API
    Route::any('api/timeline', 'CommonController@timeline');


});
