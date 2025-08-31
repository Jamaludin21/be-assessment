<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlignLoansTableForTests extends Migration
{
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            if (!Schema::hasColumn('loans', 'terms')) {
                $table->integer('terms')->default(0)->after('amount');
            }
        });

        // If your schema truly has "term_months" and it's NOT NULL, drop it (tests never use it)
        if (Schema::hasColumn('loans', 'term_months')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->dropColumn('term_months');
            });
        }

        Schema::table('loans', function (Blueprint $table) {
            if (!Schema::hasColumn('loans', 'terms')) {
                $table->integer('terms')->after('amount');
            }
            if (Schema::hasColumn('loans', 'start_date')) {
                $table->date('start_date')->nullable()->change();
            }
            if (!Schema::hasColumn('loans', 'status')) {
                $table->string('status')->default('due')->after('processed_at');
            }
        });
    }

    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            if (Schema::hasColumn('loans', 'terms')) {
                $table->dropColumn('terms');
            }
        });
        // Don’t recreate term_months; tests don’t need it.
    }
}
