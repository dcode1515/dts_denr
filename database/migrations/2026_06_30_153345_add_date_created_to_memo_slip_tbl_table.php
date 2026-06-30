<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateCreatedToMemoSlipTblTable extends Migration
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
                $table->date('date_created')->after('memo_slip_name');
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
                        $table->dropColumn('date_created');

        });
    }
}
