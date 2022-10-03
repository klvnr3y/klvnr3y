<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('histories', function (Blueprint $table) {
            $table->id();

            $table->morphs('historyable');
            $table->string('action')->nullable();
            $table->longText('from_value')->nullable();
            $table->longText('to_value')->nullable();

            $table->ipAddress('ip_address')->nullable();
            $table->text('browser')->nullable();
            $table->text('address')->nullable();
            $table->string('longitude', 20)->nullable();
            $table->string('latitude', 20)->nullable();

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
        Schema::dropIfExists('histories');
    }
}