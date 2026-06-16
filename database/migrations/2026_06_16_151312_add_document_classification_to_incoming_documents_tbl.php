<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocumentClassificationToIncomingDocumentsTbl extends Migration
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
             $table->string('document_classification')
                  ->nullable()
                  ->after('document_type_id');
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
             $table->dropColumn('document_classification');
        });
    }
}
