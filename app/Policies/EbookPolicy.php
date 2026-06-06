<?php

namespace App\Policies;

use App\Models\Ebook;
use App\Models\User;

class EbookPolicy
{
    public function manage(User $user): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Ebook $ebook): bool
    {
        return $user->isAdmin();
    }
}
