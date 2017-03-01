<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnswerUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('answer_user', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('answer_id');
            $table->unsignedSmallInteger('vote')->comment('赞同为1，反对为2');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('answer_id')->references('id')->on('answers');
            $table->unique(['user_id','answer_id','vote']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('answer_user');
    }
}
