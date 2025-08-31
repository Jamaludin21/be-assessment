<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDebitCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('debit_cards', function (Blueprint $table) {
            $table->id(); // unsigned BIGINT
            // FK harus BIGINT juga
            // $table->unsignedInteger('user_id'); // ❌
            $table->foreignId('user_id')
                ->constrained('users')           // ->references('id')->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Nomor kartu: jangan integer (bisa leading zero & > 2^31), pakai string
            // $table->unsignedInteger('number'); // ❌
            $table->string('number', 32)->index();

            $table->string('type');
            $table->dateTime('expiration_date');
            $table->dateTime('disabled_at')->nullable()->index();

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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('debit_cards');
        Schema::enableForeignKeyConstraints();
    }
}
