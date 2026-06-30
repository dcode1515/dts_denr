<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMemoSlipActionToIncomingDocumentRouteTable extends Migration
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
                        $table->text('memo_slip_action')->nullable()->after('remarks');


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
                        $table->dropColumn('memo_slip_action');

        });
    }
}
