<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermission('payments.create');
    }

    public function updateStatus(User $user): bool
    {
        return $user->hasPermission('payments.update_status') || $user->hasPermission('payments.create');
    }

    public function delete(User $user, ?Payment $payment = null): bool
    {
        return $user->hasPermission('payments.delete');
    }
}
