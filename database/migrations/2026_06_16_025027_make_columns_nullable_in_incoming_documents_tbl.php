<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeColumnsNullableInIncomingDocumentsTbl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('incoming_documents_tbl', function (Blueprint $table) {
            //
             $table->unsignedBigInteger('document_type_id')->nullable()->change();

            $table->string('sender_name')->nullable()->change();
            $table->string('subject')->nullable()->change();

            $table->date('date_receive')->nullable()->change();
            $table->time('time_receive')->nullable()->change();

            $table->date('date_release')->nullable()->change();
            $table->time('time_release')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('incoming_documents_tbl', function (Blueprint $table) {
            //
               $table->unsignedBigInteger('document_type_id')->nullable(false)->change();

            $table->string('sender_name')->nullable(false)->change();
            $table->string('subject')->nullable(false)->change();

            $table->date('date_receive')->nullable(false)->change();
            $table->time('time_receive')->nullable(false)->change();

            $table->date('date_release')->nullable(false)->change();
            $table->time('time_release')->nullable(false)->change();
        });
    }
}
