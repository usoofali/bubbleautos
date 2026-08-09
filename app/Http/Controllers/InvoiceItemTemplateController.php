<?php

namespace App\Http\Controllers;

use App\Models\InvoiceItemTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceItemTemplateController extends Controller
{
    /**
     * Display a listing of the invoice item templates.
     */
    public function index(): Response
    {
        $templates = InvoiceItemTemplate::orderBy('description')->get();

        return Inertia::render('InvoiceItemTemplates/Index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Store a newly created invoice item template in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255', 'unique:invoice_item_templates,description'],
            'default_amount' => ['required', 'numeric', 'min:0'],
        ]);

        InvoiceItemTemplate::create($validated);

        return redirect()->back()->with('success', 'Preset invoice item created successfully.');
    }

    /**
     * Update the specified invoice item template in storage.
     */
    public function update(Request $request, InvoiceItemTemplate $invoiceItemTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255', 'unique:invoice_item_templates,description,' . $invoiceItemTemplate->id],
            'default_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $invoiceItemTemplate->update($validated);

        return redirect()->back()->with('success', 'Preset invoice item updated successfully.');
    }

    /**
     * Remove the specified invoice item template from storage.
     */
    public function destroy(InvoiceItemTemplate $invoiceItemTemplate): RedirectResponse
    {
        $invoiceItemTemplate->delete();

        return redirect()->back()->with('success', 'Preset invoice item deleted successfully.');
    }
}
