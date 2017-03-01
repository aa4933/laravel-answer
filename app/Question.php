<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    //
    /**
     * 增加问题
     */
    public function add()
    {
        /*用户是否登录*/
        if (!user_ins()->is_login())
            return ['status' => 0, 'msg' => '用户未登录'];
        /*是否传入标题*/
        if (!rq('title'))
            return ['status' => 0, 'msg' => '未传入标题'];
        /*是否传入描述与保存*/
        if (rq('desc'))
            $this->desc = rq('desc');
        $this->user_id = session('user_id');
        $this->title = rq('title');
        /*返回信息*/
        return $this->save() ?
            ['status' => 1, 'id' => $this->id] :
            ['status' => 0, 'msg' => 'db fail'];

    }

    /**
     * 修改问题
     */
    public function change()
    {
        /*是否登录*/
        if (!user_ins()->is_login())
            return ['status' => 0, 'msg' => '没有登录'];

        /*是否传来id*/
        if (!rq('id'))
            return ['status' => 0, 'msg' => '没有传入id'];

        /*传来的id问题是否存在*/
        $question = $this->find(rq('id'));
        if (!$question)
            return ['status' => 0, 'msg' => '传入的id不存在'];

        /*问题是否有操控权限*/
        if ($question->user_id != session('user_id'))
            return ['status' => 0, 'msg' => '您没有操控权限'];

        /*问题修改*/
        if (rq('title'))
            $question->title = rq('title');
        if (rq('desc'))
            $question->desc = rq('desc');

        /*问题保存*/
        return $question->save() ?
            ['status' => 1, 'id' => $question->id] :
            ['status' => 0, 'msg' => 'db fail'];
    }

    /**
     * 查询问题
     */
    public function look()
    {
        /*是否只查询一个问题*/
        if (rq('id'))
            return ['status' => 1, 'data' => $this->find(rq('id'))];
        /*当前所呆的页面数*/
        list($limit, $skip) = paginate(rq('page'), rq('limit'));
        /*分页查询*/
        $result = $this
            ->orderBy('created_at')
            ->skip($skip)
            ->limit($limit)
            ->get(['id', 'user_id', 'desc', 'title', 'created_at', 'updated_at'])
            ->keyBy('id');
        /*返回数据*/
        return ['status' => 1, 'data' => $result];

    }

    /**
     * 删除问题
     */
    public function del()
    {
        /*是否登录*/
        if (!user_ins()->is_login())
            return ['status' => 0, 'msg' => '没有登录'];
        /*是否传入id*/
        if (!rq('id'))
            return ['status' => 0, 'msg' => '没有传入id'];
        /*传入的id问题是否存在*/
        $question = $this->find(rq('id'));
        if (!$question)
            return ['status' => 0, 'msg' => '传入的问题id不存在'];
        /*用户是否有权限删除*/
        if ($question->user_id != session('user_id'))
            return ['status' => 0, 'msg' => '用户没有权限删除'];
        /*删除*/
        return $question->delete() ?
            ['status' => 1, 'id' => $question->id] :
            ['status' => 0, 'msg' => 'db delete fail'];

    }
}
