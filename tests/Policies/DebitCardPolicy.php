<?php

namespace Tests\Policies;

use App\Models\DebitCard;
use App\Models\User;

class DebitCardPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // izinkan listing
    }

    // $target bisa null / class-string / instance
    public function view(User $user, $target = null): bool
    {
        if ($target instanceof DebitCard) {
            return $target->user_id === $user->id;
        }
        // kalau class-string (mis. DebitCard::class) atau null, lolos
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, $target = null): bool
    {
        if ($target instanceof DebitCard) {
            return $target->user_id === $user->id;
        }
        return true;
    }

    public function delete(User $user, $target = null): bool
    {
        if ($target instanceof DebitCard) {
            return $target->user_id === $user->id;
        }
        return true;
    }
}
