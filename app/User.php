<?php

namespace App;

use Hash;
use Illuminate\Support\Facades\Session;
use Request;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * 注册api
     */
    public function signup()
    {


        /*检查用户名和密码是否为空*/
        $check_user_pass = $this->check_user_pass();
        if (!$check_user_pass)
            return ['status' => 0, 'msg' => '不能为空'];
        $username = $check_user_pass[0];
        $password = $check_user_pass[1];

        /*检查用户名是否存在*/
        $user_exists = $this
            ->where('username', $username)
            ->exists();

        if ($user_exists)
            return ['status' => 0, 'msg' => '用户名已经存在'];

        /*加密密码*/
        $password_hash = bcrypt($password);
        /*存入数据库*/
        $this->username = $username;
        $this->password = $password_hash;
        if ($this->save())
            return ['status' => 1, 'id' => $this->id];
        else
            return ['status' => 0, 'msg' => 'db fail'];

    }

    /**
     * 登录api
     */
    public function login()
    {

        /*检查用户名和密码是否为空*/
        $check_user_pass = $this->check_user_pass();
        if (!$check_user_pass)
            return ['status' => 0, 'msg' => '不能为空'];
        $username = $check_user_pass[0];
        $password = $check_user_pass[1];

        /*检查用户是否存在*/
        $user = $this->where('username', $username)->first();
        if (!$user)
            return ['status' => 0, 'msg' => '用户不存在'];

        /*检查密码是否正确*/
        $password_hash = $user->password;
        if (!Hash::check($password, $password_hash))
            return ['status' => 0, 'msg' => '密码不正确'];

        /*存入session*/
        session()->put('username', $user->username);
        session()->put('user_id', $user->id);

        return ['status' => 1, 'id' => $user->id];
    }

    /**
     * 登出api
     */
    public function logout()
    {

        /*销毁session*/
        session()->forget('username');
        session()->forget('user_id');


        return ['status'=>'1'];
    }

    /**
     * 是否登录判断
     */
    public function is_login(){

        return session('user_id')? :false;
    }
    /**
     * 检查方法
     * @return array|bool
     */
    public function check_user_pass()
    {
        $username = rq('username');
        $password = rq('password');

        if (!$username || !$password)
            return false;

        return [$username, $password];
    }
}
