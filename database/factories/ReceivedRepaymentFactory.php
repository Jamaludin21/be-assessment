<?php

use App\Models\ReceivedRepayment;
use App\Models\Loan;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReceivedRepaymentFactory extends Factory
{
    protected $model = ReceivedRepayment::class;

    public function definition()
    {
        return [
            'loan_id'       => Loan::factory(),
            'amount'        => 1000,
            'currency_code' => Loan::CURRENCY_VND,
            'received_at'   => '2020-02-20 00:00:00',
        ];
    }
}
