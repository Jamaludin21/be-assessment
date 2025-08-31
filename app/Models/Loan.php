<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    public const STATUS_DUE = 'due';
    public const STATUS_REPAID = 'repaid';

    public const CURRENCY_SGD = 'SGD';
    public const CURRENCY_VND = 'VND';

    protected $table = 'loans';

    protected $fillable = [
        'user_id',
        'amount',
        'terms',
        'outstanding_amount',
        'currency_code',
        'processed_at',
        'status',
    ];

    protected $dates = ['processed_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scheduledRepayments(): HasMany
    {
        return $this->hasMany(ScheduledRepayment::class, 'loan_id')
            ->orderBy('due_date', 'asc');
    }

    public function receivedRepayments(): HasMany
    {
        return $this->hasMany(ReceivedRepayment::class, 'loan_id');
    }
}
