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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('user_type')->nullable();
            $table->json('before_data')->nullable();
            $table->json('after_data')->nullable(); 
            $table->string('action')->nullable();
            $table->string('table_name')->nullable();
            $table->unsignedBigInteger('table_id')->nullable();
            $table->string('browser')->nullable();
            $table->string('device')->nullable();
            $table->string('platform')->nullable();
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
        Schema::dropIfExists('activity_logs');
    }
};
