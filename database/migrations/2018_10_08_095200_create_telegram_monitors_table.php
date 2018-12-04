<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTelegramMonitorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('telegram_monitors', function (Blueprint $table) {
            $table->integer('telegram_id')->unsigned();
            $table->foreign('telegram_id')->
                references('id')->
                on('telegrams');
            $table->integer('monitor_id')->unsigned();
            $table->foreign('monitor_id')->
                references('id')->
                on('monitors');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('telegram_monitors');
    }
}
