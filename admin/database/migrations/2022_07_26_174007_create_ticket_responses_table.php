<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_responses', function (Blueprint $table) {
            $table->id();

            $table->integer('ticket_id')->nullable();
            $table->integer('submitted_by')->nullable();
            $table->longText('response')->nullable();
            $table->longText('attachment_name')->nullable();
            $table->longText('attachment_url')->nullable();
            $table->boolean('is_sensitive')->default(0)->nullable();
            $table->boolean('is_pan')->default(0)->nullable();

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
        Schema::dropIfExists('ticket_responses');
    }
}