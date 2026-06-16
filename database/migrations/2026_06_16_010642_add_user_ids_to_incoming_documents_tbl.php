<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdsToIncomingDocumentsTbl extends Migration
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
             $table->unsignedBigInteger('receive_user_id')->nullable()->after('status');
            $table->unsignedBigInteger('release_user_id')->nullable()->after('receive_user_id');

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
               $table->dropColumn([
                'receive_user_id',
                'release_user_id',
            ]);
        });
    }
}
