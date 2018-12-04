<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConversationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('telegram_id')->unsigned();
            $table->foreign('telegram_id')->
                references('id')->
                on('telegrams');
            $table->integer('from_id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->boolean('is_bot');
            $table->string('language_code')->nullable();
            $table->string('text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conversations');
    }
}
