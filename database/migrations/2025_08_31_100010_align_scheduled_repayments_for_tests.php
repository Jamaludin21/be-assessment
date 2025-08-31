<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlignScheduledRepaymentsForTests extends Migration
{
    public function up()
    {
        Schema::table('scheduled_repayments', function (Blueprint $table) {
            if (!Schema::hasColumn('scheduled_repayments', 'outstanding_amount')) {
                $table->integer('outstanding_amount')->after('amount');
            }
            if (Schema::hasColumn('scheduled_repayments', 'paid_amount')) {
                $table->dropColumn('paid_amount');
            }
            if (!Schema::hasColumn('scheduled_repayments', 'currency_code')) {
                $table->string('currency_code', 3)->after('outstanding_amount');
            }
            if (!Schema::hasColumn('scheduled_repayments', 'status')) {
                $table->string('status')->default('due')->after('due_date');
            }
            if (Schema::hasColumn('scheduled_repayments', 'is_paid')) {
                $table->dropColumn('is_paid');
            }
        });
    }

    public function down()
    {
        Schema::table('scheduled_repayments', function (Blueprint $table) {
            if (Schema::hasColumn('scheduled_repayments', 'status')) $table->dropColumn('status');
            if (Schema::hasColumn('scheduled_repayments', 'currency_code')) $table->dropColumn('currency_code');
            if (Schema::hasColumn('scheduled_repayments', 'outstanding_amount')) $table->dropColumn('outstanding_amount');
        });
    }
}
