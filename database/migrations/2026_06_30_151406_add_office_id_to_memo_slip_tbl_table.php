<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOfficeIdToMemoSlipTblTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('memo_slip_tbl', function (Blueprint $table) {
            //
                        $table->unsignedBigInteger('office_id')->after('id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('memo_slip_tbl', function (Blueprint $table) {
            //
                        $table->dropColumn('office_id');

        });
    }
}
