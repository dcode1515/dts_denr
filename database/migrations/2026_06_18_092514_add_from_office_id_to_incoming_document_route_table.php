<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFromOfficeIdToIncomingDocumentRouteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('incoming_document_route', function (Blueprint $table) {
            //
              $table->unsignedBigInteger('from_office_id')->nullable()->after('office_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('incoming_document_route', function (Blueprint $table) {
            //
          
            $table->dropColumn('from_office_id');
        });
    }
}
