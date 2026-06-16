<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActionIdToIncomingDocumentRouteTable extends Migration
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
              $table->unsignedBigInteger('action_id')
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
        Schema::table('incoming_document_route', function (Blueprint $table) {
            //
                        $table->dropColumn('action_id');

        });
    }
}
