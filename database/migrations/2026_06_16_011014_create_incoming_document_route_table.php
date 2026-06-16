<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomingDocumentRouteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incoming_document_route', function (Blueprint $table) {
            $table->id();

            // References
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('office_id');
            $table->unsignedBigInteger('action_id');
            $table->unsignedBigInteger('receive_user_id')->nullable();
               

            // Routing timestamps
            $table->dateTime('date_forwarded')->nullable();
            $table->dateTime('date_receive')->nullable();
            $table->dateTime('date_document_out')->nullable();

            // Additional info
            $table->text('remarks')->nullable();

            $table->string('status')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('incoming_document_route');
    }
}
