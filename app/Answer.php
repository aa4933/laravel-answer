<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    /**
     * 回答问题API
     */
    public function add()
    {
        /*检查用户是否登录*/
        if (!user_ins()->is_login())
            return ['status' => 0, 'msg' => '没有登录'];
        /*检查是否传入问题id与问题内容*/
        if (!rq('question_id') || !rq('content'))
            return ['status' => 0, 'msg' => '需要同时传入问题id与回答内容'];
        /*检查问题是否存在*/
        $question = question_ins()->find(rq('question_id'));
        if (!$question)
            return ['status' => 0, 'msg' => '问题不存在'];
        /*检查用户是否重复回答*/
        $r = $this
            ->where(['user_id' => session('user_id'), 'question_id' => rq('question_id')])
            ->count();
        if ($r)
            return ['status' => 0, 'msg' => '您不能重复回答'];
        /*插入回答信息*/
        $this->content = rq('content');
        $this->question_id = rq('question_id');
        $this->user_id = session('user_id');
        return $this->save() ?
            ['status' => 1, 'id' => $this->id] :
            ['status' => 0, 'msg' => 'db add fail'];
    }

    /**
     * 修改回答API
     */
    public function change()
    {
        /*是否登录*/
        if (!user_ins()->is_login())
            return ['status' => 0, 'msg' => '没有登录'];
        /*是否传入了回答id与修改内容*/
        if (!rq('id') || !rq('content'))
            return ['status' => 0, 'msg' => '必须传入回答id与回答内容'];
        /*是否存在这个回答*/
        $answer = $this->find(rq('id'));
        if (!$answer)
            return ['status' => 0, 'msg' => '不存在这个回答'];
        /*是否有权限修改*/
        if ($answer->user_id != session('user_id'))
            return ['status' => 0, 'msg' => '您没有权限修改'];
        /*修改保存*/
        $answer->content = rq('content');
        return $answer->save() ?
            ['status' => 1, 'id' => $answer->id] :
            ['status' => 0, 'msg' => 'db update fail'];

    }

    /**
     * 查看回答API
     */
    public function look()
    {
        /*是否传入回答id或者问题id*/
        if (!rq('id') && !rq('question_id'))
            return ['status' => 0, 'msg' => '回答id与问题id必须传入一个'];
        /*查看单个回答*/
        if (rq('id')) {
            /*回答不存在*/
            $answer = $this->find(rq('id'));
            if (!$answer)
                return ['status' => 0, 'msg' => '回答不存在'];

            return ['status' => 1, 'data' => $answer];
        }
        /*查看问题下的所有回答*/
        if (rq('question_id')) {
            /*问题不存在*/
            $question = question_ins()->find(rq('question_id'));
            if (!$question)
                return ['status' => 0, 'msg' => '问题不存在'];

            $answers = $this
                ->where('question_id', rq('question_id'))
                ->get()
                ->keyBy('id');
            return ['status' => 1, 'data' => $answers];
        }


    }

    /**
     * 删除回答API
     */
    public function del()
    {
        /*是否登录*/
        if (!user_ins()->is_login())
            return ['status' => 0, 'msg' => '没有登录'];
        /*是否传入回答id*/
        if (!rq('id'))
            return ['status' => 0, 'msg' => '没有传入id'];
        /*回答id是否存在*/
        $answer = $this->find(rq('id'));
        if (!$answer)
            return ['status' => 0, 'msg' => '回答不存在'];
        /*是否有权限删除*/
        if ($answer->user_id != session('user_id'))
            return ['status' => 0, 'msg' => '没有权限删除'];
        /*删除*/
        return $answer->delete() ?
            ['status' => 1, 'id' => $answer->id] :
            ['status' => 0, 'msg' => 'db delete fail'];
    }

    /**
     * 通用-投票API
     * @return array
     */
    public function vote()
    {
        /*是否登录*/
        if (!user_ins()->is_login())
            return ['status' => 0, 'msg' => '没有登录'];

        /*如果没有传入的id或者没有投票类型，返回错误*/
        if (!rq('id') || !rq('vote'))
            return ['status' => 0, 'msg' => '必须有投票id或者投票类型'];
        /*问题不能不存在*/
        $answer = $this->find(rq('id'));
        if (!$answer)
            return ['status' => 0, 'msg' => '回答不存在'];

        $vote = rq('vote') <= 1 ? 1 : 2;

        /*用户是否在相同的问题下投过,如果有，直接用delete会删除*/
        $answer->users()->newPivotStatement()
            ->where('user_id', session('user_id'))
            ->where('answer_id', rq('id'))
            ->delete();

        $answer->users()->attach(session('user_id'), ['vote' => $vote]);


        return ['status' => 1];

    }

    /**
     * 多对多关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this
            ->belongsToMany('App\User')
            ->withPivot('vote')
            ->withTimestamps();
    }
}
