<?php

namespace Tests\Policies;

use App\Models\DebitCard;
use App\Models\DebitCardTransaction;
use App\Models\User;

class DebitCardTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, $target = null): bool
    {
        if ($target instanceof DebitCardTransaction) {
            // pastikan relasi ada: debitCard()
            return optional($target->debitCard)->user_id === $user->id;
        }
        return true;
    }

    public function create(User $user, $target = null): bool
    {
        // kadang controller panggil can('create', DebitCardTransaction::class)
        // → izinkan; validasi kepemilikan kita cover saat memakai instance/nilai debit_card_id
        if ($target instanceof DebitCard) {
            return $target->user_id === $user->id;
        }
        return true;
    }
}
