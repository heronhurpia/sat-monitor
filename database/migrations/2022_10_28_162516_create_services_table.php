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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
			$table->string('service_type')->default("");
            $table->string('codec')->default("");
            $table->integer('video_pid')->unsigned();
            $table->integer('pcr_pid')->unsigned();
            $table->integer('epg_pid')->unsigned();
            $table->integer('svcid')->unsigned();
			$table->integer('transponder_id')->unsigned();
			$table->foreign('transponder_id')->references('id')->on('transponders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('services');
    }
};
