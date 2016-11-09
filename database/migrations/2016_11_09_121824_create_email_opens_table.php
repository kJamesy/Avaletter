<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailOpensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_opens', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('email_injection_id')->unsigned();
            $table->string('ip_address')->nullable();
            $table->string('country')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device')->nullable();
            $table->string('platform')->nullable();
            $table->string('browser')->nullable();
            $table->integer('hits');
            $table->dateTime('opened_at');
            $table->timestamps();

            $table->foreign('email_injection_id')->references('id')->on('email_injections')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('email_opens');
    }
}
