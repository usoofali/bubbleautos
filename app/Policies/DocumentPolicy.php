<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('orders.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('documents.upload');
    }

    public function delete(User $user, ?Document $document = null): bool
    {
        return $user->hasPermission('documents.delete');
    }
}
