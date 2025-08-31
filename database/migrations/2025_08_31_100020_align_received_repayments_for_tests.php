<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlignReceivedRepaymentsForTests extends Migration
{
    public function up()
    {
        Schema::table('received_repayments', function (Blueprint $table) {
            if (Schema::hasColumn('received_repayments', 'paid_at')) {
                $table->renameColumn('paid_at', 'received_at');
            } elseif (!Schema::hasColumn('received_repayments', 'received_at')) {
                $table->dateTime('received_at');
            }
            if (!Schema::hasColumn('received_repayments', 'currency_code')) {
                $table->string('currency_code', 3)->after('amount');
            }
            if (Schema::hasColumn('received_repayments', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }

    public function down()
    {
        Schema::table('received_repayments', function (Blueprint $table) {
            if (Schema::hasColumn('received_repayments', 'currency_code')) {
                $table->dropColumn('currency_code');
            }
            if (Schema::hasColumn('received_repayments', 'received_at') && !Schema::hasColumn('received_repayments', 'paid_at')) {
                $table->renameColumn('received_at', 'paid_at');
            }
        });
    }
}
