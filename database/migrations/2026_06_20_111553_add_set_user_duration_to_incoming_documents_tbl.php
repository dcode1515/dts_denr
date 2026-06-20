<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSetUserDurationToIncomingDocumentsTbl extends Migration
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
             $table->unsignedBigInteger('set_user_duration_id')->nullable()->after('id');
            $table->string('duration')->nullable()->after('set_user_duration_id');
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
             $table->dropColumn(['set_user_duration_id', 'duration']);
        });
    }
}
