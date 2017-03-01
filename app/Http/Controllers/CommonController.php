<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class CommonController extends Controller
{
    /**
     * 时间线API
     */
    public function timeline()
    {
        list($limit, $skip) = paginate(rq('page'), rq('limit'));

        /*获取API*/
        $question = question_ins()
            ->limit($limit)
            ->skip($skip)
            ->orderBy('created_at', 'desc')
            ->get();

        $answer = answer_ins()
            ->limit($limit)
            ->skip($skip)
            ->orderBy('created_at', 'desc')
            ->get();

        /*合并数据*/
        $data=$question->merge($answer);
        /*数据排序*/
        $data=$data->sortByDesc(function ($item){
            return $item->created_at;
        });
        /*返回数据*/
        $data=$data->values()->all();
        return ['status' => 1, 'data' => $data] ;

    }
}
