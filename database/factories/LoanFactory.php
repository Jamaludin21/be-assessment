<?php

use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanFactory extends Factory
{
    protected $model = Loan::class;

    public function definition()
    {
        $amount = 3000;
        $terms  = 3;
        return [
            'user_id'            => User::factory(),
            'amount'             => $amount,
            'terms'              => $terms,
            'outstanding_amount' => $amount,
            'currency_code'      => Loan::CURRENCY_VND,
            'processed_at'       => '2020-01-20',
            'status'             => Loan::STATUS_DUE,
        ];
    }
}
