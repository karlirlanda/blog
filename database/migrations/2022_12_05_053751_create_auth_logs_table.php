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
        Schema::create('auth_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('user_type')->nullable();
            $table->string('action');
            $table->string('token')->nullable();
            $table->string('ip_address');
            $table->string('status')->nullable();
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
        Schema::dropIfExists('auth_logs');
    }
};
