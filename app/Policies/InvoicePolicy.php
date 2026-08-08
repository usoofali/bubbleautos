<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function view(User $user, ?Invoice $invoice = null): bool
    {
        return $user->hasPermission('invoices.view') || $user->hasPermission('orders.view');
    }

    public function manageItems(User $user, ?Invoice $invoice = null): bool
    {
        return $user->hasPermission('invoices.manage_items') || $user->hasPermission('orders.edit');
    }
}
