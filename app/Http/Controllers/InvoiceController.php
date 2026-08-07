<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(protected InvoiceService $invoiceService) {}

    public function addItem(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice->order);

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        $this->invoiceService->addItem($invoice, $validated);

        return back()->with('success', 'Invoice item added successfully.');
    }

    public function updateItem(Request $request, InvoiceItem $item): RedirectResponse
    {
        $this->authorize('update', $item->invoice->order);

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        $this->invoiceService->updateItem($item, $validated);

        return back()->with('success', 'Invoice item updated successfully.');
    }

    public function removeItem(InvoiceItem $item): RedirectResponse
    {
        $this->authorize('update', $item->invoice->order);

        $this->invoiceService->removeItem($item);

        return back()->with('success', 'Invoice item removed successfully.');
    }
}
