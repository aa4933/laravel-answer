<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    /**
     * 评论增加API
     */
    public function add()
    {
        /*用户是否登录*/
        if (!user_ins()->is_login())
            return err('没有登录');
        /*是否有评论内容*/
        if (!rq('content'))
            return err('没有评论内容');
        /*不能同时不存在问题id与回答id*/
        if (!rq('question_id') && !rq('answer_id'))
            return err('不存在问题id与回答id');
        /*不能同时存在问题id与回答id*/
        if (!rq('question_id') && !rq('answer_id'))
            return err('不存在问题id与回答id');
        /*问题id*/
        if (rq('question_id')) {
            $question = question_ins()->find(rq('question_id'));
            if (!$question)
                return err('不存在问题');
            $this->question_id = rq('question_id');
        } /*回答id*/
        else {
            $answer = answer_ins()->find(rq('answer_id'));
            if (!$answer)
                return err('不存在回答');
            $this->answer_id = rq('answer_id');
        }
        /*评论中的评论*/
        if (rq('reply_to')) {
            $reply = $this->find(rq('reply_to'));
            if (!$reply)
                return err('不存在这个评论');
            if ($reply->user_id == session('user_id'))
                return err('不能回复自己');
            $this->reply_to = rq('reply_to');
        }
        /*保存*/
        $this->content = rq('content');
        $this->user_id = session('user_id');
        return $this->save() ?
            suc(['id' => $this->id]) :
            err('db delete fail');
    }

    /**查看评论API
     *
     */
    public function look()
    {
        /*必须存在question或者answer的信息*/
        if (!rq('question_id') && !rq('answer_id'))
            return err('必须存在问题或者回答的信息');
        /*有关问题的评论*/
        if (rq('question_id')) {
            $question = question_ins()->find(rq('question_id'));
            if (!$question)
                return err('不存在这个问题');
            $data = $this->where('question_id', rq('question_id'));
        } /*有关回答的评论*/
        else {
            $answer = answer_ins()->find(rq('answer_id'));
            if (!$answer)
                return err('不存在这个回答');
            $data = $this->where('answer_id', rq('answer_id'));
        }
        /*返回值*/
        $data = $data->get()->keyBy('id');

        return suc(['data' => $data]);
    }

    /**
     * 删除API
     */
    public function del()
    {
        /*用户是否登录*/
        if (!user_ins()->is_login())
            return err('用户请登录');
        /*是否传入ID*/
        if (!rq('id'))
            return err('请传入需要删除的信息');
        /*传入的ID是否有效*/
        $comment = $this->find(rq('id'));
        if (!$comment)
            return err('传入的信息无效');
        /*用户是否有权限删除*/
        if ($comment->user_id != session('user_id'))
            return err('您没有权限删除');
        /*删除相关的评论*/
        $this->where('reply_to', rq('id'))->delete();
        return $comment->delete() ?
            suc(['id' => $comment->id]) :
            err('db delete fail');
    }
}
