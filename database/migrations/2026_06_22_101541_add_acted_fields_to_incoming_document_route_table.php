<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActedFieldsToIncomingDocumentRouteTable extends Migration
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
              $table->dateTime('date_acted')->nullable();
            $table->string('acted_documents')->nullable();
            $table->unsignedBigInteger('user_acted_id')->nullable();
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
              $table->dropColumn([
                'date_acted',
                'acted_documents',
                'user_acted_id',
            ]);
        });
    }
}
