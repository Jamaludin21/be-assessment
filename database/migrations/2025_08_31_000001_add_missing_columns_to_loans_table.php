<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToLoansTable extends Migration
{
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            if (!Schema::hasColumn('loans', 'terms')) {
                $table->integer('terms')->default(0)->after('amount'); // DEFAULT!
            }
            if (!Schema::hasColumn('loans', 'outstanding_amount')) {
                $table->integer('outstanding_amount')->default(0)->after('terms'); // DEFAULT!
            }
            if (!Schema::hasColumn('loans', 'currency_code')) {
                $table->string('currency_code', 3)->default('VND')->after('outstanding_amount'); // DEFAULT!
            }
            if (!Schema::hasColumn('loans', 'processed_at')) {
                // SQLite can’t default to CURRENT_DATE easily; use a harmless fixed date.
                $table->date('processed_at')->default('1970-01-01')->after('currency_code'); // DEFAULT!
            }
            if (!Schema::hasColumn('loans', 'status')) {
                $table->string('status', 20)->default('due')->after('processed_at'); // DEFAULT!
            }
        });
    }

    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            if (Schema::hasColumn('loans', 'status')) $table->dropColumn('status');
            if (Schema::hasColumn('loans', 'processed_at')) $table->dropColumn('processed_at');
            if (Schema::hasColumn('loans', 'currency_code')) $table->dropColumn('currency_code');
            if (Schema::hasColumn('loans', 'outstanding_amount')) $table->dropColumn('outstanding_amount');
            if (Schema::hasColumn('loans', 'terms')) $table->dropColumn('terms');
        });
    }
}
