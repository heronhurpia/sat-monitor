<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transponders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('frequency')->unsigned();
            $table->integer('symbol_rate')->unsigned();
            $table->string('polarity');
            $table->integer('satellite_id')->unsigned()->default('1');
            $table->integer('tsid')->unsigned();
            $table->string('orbital')->default("70W");
            $table->string('dvb_mode');
            $table->integer('network_id')->unsigned();
            $table->integer('onid')->unsigned();
            $table->integer('quality')->unsigned();
            $table->integer('strength')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transponders');
    }
};
