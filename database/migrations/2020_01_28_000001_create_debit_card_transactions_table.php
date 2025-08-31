<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDebitCardTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('debit_card_transactions', function (Blueprint $table) {
            $table->id(); // unsigned BIGINT

            // $table->unsignedInteger('debit_card_id'); // ❌
            $table->foreignId('debit_card_id')
                ->constrained('debit_cards')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->integer('amount');       // atau bigInteger kalau perlu nominal besar
            $table->string('currency_code'); // bisa batasi 3 char kalau mau: ->char('currency_code', 3)
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
        Schema::dropIfExists('debit_card_transactions');
        Schema::enableForeignKeyConstraints();
    }
}
