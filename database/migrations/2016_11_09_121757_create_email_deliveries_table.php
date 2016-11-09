<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_deliveries', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('email_injection_id')->unsigned();
            $table->dateTime('delivered_at');
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
        Schema::drop('email_deliveries');
    }
}
