<?php

namespace App;

use Hash;
use Illuminate\Support\Facades\Session;
use Request;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * 获取用户有关的信息
     */
    public function look()
    {
        /*是否传入用户id*/
        if (!rq("id"))
            return err('未指定用户');
        /*获取用户表可获取的字段*/
        $get=["id","username","intro","url_head"];
        $user=$this->find(rq('id'),$get);
        if (!$user)
            return err('用户不存在');
        $data=$user->toArray();
        /*获取用户的回答*/
        $answer_user=answer_ins()->where('user_id',rq('id'))->count();
        /*获取用户的问题*/
        $question_user=question_ins()->where('user_id',rq('id'))->count();

        $data['answer_user']=$answer_user;
        $data['question_user']=$question_user;

        return suc($data);

    }

    /**
     * 注册api
     */
    public function signup()
    {


        /*检查用户名和密码是否为空*/
        $check_user_pass = $this->check_user_pass();
        if (!$check_user_pass)
            return err('不能为空');
        $username = $check_user_pass[0];
        $password = $check_user_pass[1];

        /*检查用户名是否存在*/
        $user_exists = $this
            ->where('username', $username)
            ->exists();

        if ($user_exists)
            return err('用户名已经存在');

        /*加密密码*/
        $password_hash = bcrypt($password);
        /*存入数据库*/
        $this->username = $username;
        $this->password = $password_hash;
        if ($this->save())
            return suc(['id' => $this->id]);
        else
            return err('db fail');

    }

    /**
     * 登录api
     */
    public function login()
    {

        /*检查用户名和密码是否为空*/
        $check_user_pass = $this->check_user_pass();
        if (!$check_user_pass)
            return err('不能为空');
        $username = $check_user_pass[0];
        $password = $check_user_pass[1];

        /*检查用户是否存在*/
        $user = $this->where('username', $username)->first();
        if (!$user)
            return err('用户不存在');

        /*检查密码是否正确*/
        $password_hash = $user->password;
        if (!Hash::check($password, $password_hash))
            return err('密码不正确');

        /*存入session*/
        session()->put('username', $user->username);
        session()->put('user_id', $user->id);

        return suc(['id' => $user->id]);
    }

    /**
     * 修改密码
     */
    public function change_password()
    {
        /*检测用户是否登录*/
        if (!user_ins()->is_login())
            return err("用户未登录");
        /*是否有传入原来密码与要修改的密码*/
        if (!rq("new_password") || !rq("old_password"))
            return err("没有输入原密码和新密码");
        /*用户密码是否正确*/
        $user = $this->find(session("user_id"));

        if (!Hash::check(rq("old_password"), $user->password))
            return err('密码不正确');
        /*修改密码*/
        $user->password = bcrypt(rq("new_password"));


        if ($user->save())
            return suc(['id' => $this->id]);
        else
            return err('db fail');
    }

    /**
     * 找回密码
     */
    public function find_password()
    {
        /*校验机器人*/
        if ($this->is_robot(2))
            return err("您太频繁");

        /*是否传入手机*/
        if (!rq("phone"))
            return err("您得输入手机号码");
        /*传入的手机是否存在*/
        $user = $this->where(
            ["phone" => rq("phone"),
                "id" => session("user_id")
            ])->first();
        if (!$user)
            return err("您的账号没有这个手机号码存在");
        /*生成手机验证码*/
        $captcha = $this->generate_captcha();
        /*存储手机验证码*/
        $user->phone_captcha = $captcha;

        if ($user->save()) {
            $this->sms_phone();
            /*更新机器人*/
            $this->update_robot();
            return suc();
        } else
            return err('db fail');
    }

    /**
     * 验证找回密码
     */
    public function check_find_password()
    {
        /*校验机器人*/
        if ($this->is_robot())
            return err("您太频繁");

        /*必须传入手机，手机验证码，更改的新密码*/
        if (!rq("phone") || !rq("phone_captcha") || !rq("new_password"))
            return err("您的参数不全");
        /*校验验证码是否正确*/
        $user = $this->where([
            "phone" => rq("phone"),
            "phone_captcha" => rq("phone_captcha"),
            "id" => session("user_id")
        ])->first();
        if (!$user)
            return err("信息错误");

        /*更改密码*/
        $user->password = bcrypt(rq("new_pssword"));
        /*更新机器人*/
        $this->update_robot();
        return $user->save() ? suc() : err('db fail');

    }

    /**
     * 校验是否是机器人
     */
    public function is_robot($time = 10)
    {
        if (!session("last_time"))
            return false;

        $start_time = time();

        if ($start_time - session("last_time") > $time)
            return false;

        return true;

    }

    /**
     * 更新时间
     */
    public function update_robot()
    {
        session()->set("last_time", time());
    }

    /**
     * 模拟短信接口
     */
    private function sms_phone()
    {
        return true;
    }

    /**
     *  生成手机验证码
     */
    private function generate_captcha()
    {
        return rand(1000, 9999);
    }

    /**
     * 登出api
     */
    public function logout()
    {

        /*销毁session*/
        session()->forget('username');
        session()->forget('user_id');


        return suc();
    }

    /**
     * 是否登录判断
     */
    public function is_login()
    {

        return session('user_id') ?: false;
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

    /**
     * 多对多关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function answers()
    {
        return $this
            ->belongsToMany('App/Answer')
            ->withPivot('vote')
            ->withTimestamps();
    }
}
