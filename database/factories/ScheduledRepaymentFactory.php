<?php

use App\Models\ScheduledRepayment;
use App\Models\Loan;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduledRepaymentFactory extends Factory
{
    protected $model = ScheduledRepayment::class;

    public function definition()
    {
        return [
            'loan_id'            => Loan::factory(),
            'amount'             => 1000,
            'outstanding_amount' => 1000,
            'currency_code'      => Loan::CURRENCY_VND,
            'due_date'           => '2020-02-20',
            'status'             => ScheduledRepayment::STATUS_DUE,
        ];
    }
}
