<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\ReceivedRepayment;
use App\Models\ScheduledRepayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LoanService
{
    /**
     * Create a Loan and its Scheduled Repayments
     */
    public function createLoan(User $user, int $amount, string $currencyCode, int $terms, string $processedAt): Loan
    {
        return DB::transaction(function () use ($user, $amount, $currencyCode, $terms, $processedAt) {
            $loan = Loan::create([
                'user_id'            => $user->id,
                'amount'             => $amount,
                'terms'              => $terms,
                'outstanding_amount' => $amount,
                'currency_code'      => $currencyCode,
                'processed_at'       => Carbon::parse($processedAt),
                'status'             => Loan::STATUS_DUE,
            ]);

            // split into equal parts, remainder to the last installment
            $base     = intdiv($amount, $terms);
            $remainder = $amount - ($base * $terms);

            for ($i = 1; $i <= $terms; $i++) {
                $installment = $base + ($i === $terms ? $remainder : 0);
                $dueDate     = Carbon::parse($processedAt)->addMonths($i)->toDateString();

                ScheduledRepayment::create([
                    'loan_id'            => $loan->id,
                    'amount'             => $installment,
                    'outstanding_amount' => $installment,
                    'currency_code'      => $currencyCode,
                    'due_date'           => $dueDate,
                    'status'             => ScheduledRepayment::STATUS_DUE,
                ]);
            }

            // eager load for the test's assertions
            return $loan->fresh(['scheduledRepayments']);
        });
    }

    /**
     * Repay Scheduled Repayments for a Loan
     */
    public function repayLoan(Loan $loan, int $receivedAmount, string $currencyCode, string $receivedAt): Loan
    {
        return DB::transaction(function () use ($loan, $receivedAmount, $currencyCode, $receivedAt) {
            // record the received repayment
            ReceivedRepayment::create([
                'loan_id'       => $loan->id,
                'amount'        => $receivedAmount,
                'currency_code' => $currencyCode,
                'received_at'   => Carbon::parse($receivedAt),
            ]);

            $remaining = $receivedAmount;

            // apply to each due/partial scheduled repayment in order
            $schedules = $loan->scheduledRepayments()
                ->whereIn('status', [ScheduledRepayment::STATUS_DUE, ScheduledRepayment::STATUS_PARTIAL])
                ->orderBy('due_date')
                ->lockForUpdate()
                ->get();

            foreach ($schedules as $schedule) {
                if ($remaining <= 0) break;

                $apply = min($schedule->outstanding_amount, $remaining);

                $schedule->outstanding_amount -= $apply;
                $remaining -= $apply;

                if ($schedule->outstanding_amount === 0) {
                    $schedule->status = ScheduledRepayment::STATUS_REPAID;
                } else {
                    $schedule->status = ScheduledRepayment::STATUS_PARTIAL;
                }

                $schedule->save();
            }

            // update loan outstanding and status
            $loan->outstanding_amount = max(0, $loan->outstanding_amount - $receivedAmount);
            $loan->status = $loan->outstanding_amount === 0 ? Loan::STATUS_REPAID : Loan::STATUS_DUE;
            $loan->save();

            return $loan->fresh(['scheduledRepayments']);
        });
    }
}
