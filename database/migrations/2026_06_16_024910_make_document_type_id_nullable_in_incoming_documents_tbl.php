<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeDocumentTypeIdNullableInIncomingDocumentsTbl extends Migration
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
               $table->unsignedBigInteger('document_type_id')
                  ->nullable()
                  ->change();
       
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
             $table->unsignedBigInteger('document_type_id')
                  ->nullable(false)
                  ->change();
        });
    }
}
