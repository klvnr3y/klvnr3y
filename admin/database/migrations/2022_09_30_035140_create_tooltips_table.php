<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTooltipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tooltips', function (Blueprint $table) {
            $table->id();
            $table->string('role')->nullable();
            $table->longText('selector')->nullable();
            $table->string('tooltip_type')->nullable();
            $table->longText('tooltip_color')->nullable();
            $table->string('position')->nullable();
            $table->longText('description')->nullable();
            $table->string('insert_at')->nullable();
            $table->longText('video_url')->nullable();
            $table->string('is_req')->nullable();
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
        Schema::dropIfExists('tooltips');
    }
}