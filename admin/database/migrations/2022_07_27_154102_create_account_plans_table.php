<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_plans', function (Blueprint $table) {
            $table->id();

            $table->integer('account_type_id')->unsigned();
            $table->string('type')->nullable();
            $table->string('plan')->nullable();
            $table->longText('description')->nullable();
            $table->string('amount')->nullable();
            $table->integer('index')->nullable();
            $table->string('stripe_price_id')->nullable();

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
        Schema::dropIfExists('account_plans');
    }
}