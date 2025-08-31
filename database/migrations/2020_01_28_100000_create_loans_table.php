<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id(); // unsigned BIGINT
            // WAJIB sama tipe dengan users.id
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // kolom lain (silakan sesuaikan)
            $table->integer('amount');                // nominal pinjaman
            $table->char('currency_code', 3);         // e.g. 'IDR'
            $table->tinyInteger('term_months');       // 3 atau 6
            $table->date('start_date');               // tanggal mulai/akad
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loans');
    }
}
