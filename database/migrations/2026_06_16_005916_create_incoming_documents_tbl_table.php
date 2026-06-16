<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomingDocumentsTblTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('incoming_documents_tbl', function (Blueprint $table) {
                $table->id();
                $table->string('tracking_number')->unique();
                $table->unsignedBigInteger('document_type_id');
                $table->string('sender_name');
                $table->string('subject');
                $table->date('date_receive');
                $table->time('time_receive');
                $table->date('date_release')->nullable();
                $table->time('time_release')->nullable();
                $table->string('draft_attachment')->nullable();
                $table->string('release_attachment')->nullable();
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
        Schema::dropIfExists('incoming_documents_tbl');
    }
}
