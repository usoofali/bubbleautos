<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\VehicleSale;
use App\Models\VehicleSalePayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class VehicleSaleController extends Controller
{
    /**
     * Display a listing of vehicle sales.
     */
    public function index(Request $request): InertiaResponse
    {
        $query = VehicleSale::with(['creator', 'payments'])->latest('id');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $sales = $query->paginate(15)->withQueryString();

        return Inertia::render('VehicleSales/Index', [
            'sales' => $sales,
            'filters' => [
                'search' => $request->search ?? '',
            ],
        ]);
    }

    /**
     * Show the form for creating a new vehicle sale.
     */
    public function create(): InertiaResponse
    {
        return Inertia::render('VehicleSales/Create', [
            'nextSaleNumber' => VehicleSale::generateNextSaleNumber(),
            'defaultDate' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Store a newly created vehicle sale in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_address' => 'nullable|string',
            'vehicle_make' => 'required|string|max:100',
            'vehicle_model' => 'required|string|max:100',
            'vehicle_year' => 'nullable|string|max:20',
            'vehicle_vin' => 'nullable|string|max:100',
            'vehicle_color' => 'nullable|string|max:50',
            'vehicle_description' => 'nullable|string',
            'sale_date' => 'required|date',
            'sale_amount' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:100',
            'amount_in_words' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $saleAmount = (float) $validated['sale_amount'];
        $amountPaid = (float) $validated['amount_paid'];

        if (empty($validated['amount_in_words'])) {
            $validated['amount_in_words'] = VehicleSale::convertAmountToWords($amountPaid > 0 ? $amountPaid : $saleAmount);
        }

        $validated['sale_number'] = VehicleSale::generateNextSaleNumber();
        $validated['created_by'] = $request->user()?->id;

        $sale = VehicleSale::create($validated);

        // Record initial payment receipt if amount_paid > 0
        if ($amountPaid > 0) {
            $sale->payments()->create([
                'receipt_number' => VehicleSalePayment::generateNextReceiptNumber(),
                'amount_paid' => $amountPaid,
                'payment_date' => $validated['sale_date'],
                'payment_method' => $validated['payment_method'] ?? 'bank_transfer',
                'amount_in_words' => $validated['amount_in_words'],
                'notes' => 'Initial payment upon sales registration',
                'created_by' => $request->user()?->id,
            ]);
        }

        return redirect()->route('vehicle-sales.show', $sale)
            ->with('success', 'Vehicle sale transaction recorded successfully!');
    }

    /**
     * Display the specified vehicle sale details.
     */
    public function show(VehicleSale $vehicleSale): InertiaResponse
    {
        $vehicleSale->load(['creator', 'payments.creator']);

        return Inertia::render('VehicleSales/Show', [
            'sale' => $vehicleSale,
            'companySettings' => [
                'name' => Setting::get('company_name', 'Bubble Autos Nigeria Limited'),
                'address' => Setting::get('contact_address', 'No.: 43 Abdullahi Bayero Nassarawa GRA, Kano.'),
                'phone' => Setting::get('contact_phone', '08033473516 08023370786'),
                'currency_symbol' => Setting::get('currency_symbol', '₦'),
                'currency_code' => Setting::get('currency_code', 'NGN'),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified vehicle sale.
     */
    public function edit(VehicleSale $vehicleSale): InertiaResponse
    {
        return Inertia::render('VehicleSales/Edit', [
            'sale' => $vehicleSale,
        ]);
    }

    /**
     * Update the specified vehicle sale in storage.
     */
    public function update(Request $request, VehicleSale $vehicleSale): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_address' => 'nullable|string',
            'vehicle_make' => 'required|string|max:100',
            'vehicle_model' => 'required|string|max:100',
            'vehicle_year' => 'nullable|string|max:20',
            'vehicle_vin' => 'nullable|string|max:100',
            'vehicle_color' => 'nullable|string|max:50',
            'vehicle_description' => 'nullable|string',
            'sale_date' => 'required|date',
            'sale_amount' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:100',
            'amount_in_words' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $saleAmount = (float) $validated['sale_amount'];

        if (empty($validated['amount_in_words'])) {
            $validated['amount_in_words'] = VehicleSale::convertAmountToWords($saleAmount);
        }

        $vehicleSale->update($validated);

        return redirect()->route('vehicle-sales.show', $vehicleSale)
            ->with('success', 'Vehicle sale transaction updated successfully!');
    }

    /**
     * Record a new installment payment receipt for an existing sale.
     */
    public function storePayment(Request $request, VehicleSale $vehicleSale): RedirectResponse
    {
        $validated = $request->validate([
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:100',
            'amount_in_words' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $amountPaid = (float) $validated['amount_paid'];
        $balanceDue = (float) $vehicleSale->balance_due;

        if ($amountPaid > $balanceDue + 0.01) {
            $currencySymbol = Setting::get('currency_symbol', '₦');

            return back()->withErrors([
                'amount_paid' => 'Payment amount ('.$currencySymbol.number_format($amountPaid, 2).') cannot exceed remaining balance ('.$currencySymbol.number_format($balanceDue, 2).').',
            ]);
        }

        if (empty($validated['amount_in_words'])) {
            $validated['amount_in_words'] = VehicleSale::convertAmountToWords($amountPaid);
        }

        $validated['receipt_number'] = VehicleSalePayment::generateNextReceiptNumber();
        $validated['created_by'] = $request->user()?->id;

        $vehicleSale->payments()->create($validated);

        // Update amount_paid total on master sale
        $totalPaid = $vehicleSale->payments()->sum('amount_paid');
        $vehicleSale->update(['amount_paid' => $totalPaid]);

        return redirect()->route('vehicle-sales.show', $vehicleSale)
            ->with('success', 'Payment installment recorded & receipt generated successfully!');
    }

    /**
     * Remove the specified vehicle sale from storage.
     */
    public function destroy(VehicleSale $vehicleSale): RedirectResponse
    {
        $vehicleSale->delete();

        return redirect()->route('vehicle-sales.index')
            ->with('success', 'Vehicle sale transaction deleted successfully.');
    }

    /**
     * Download the official Invoice PDF (using public/invoice.png overlay).
     */
    public function downloadInvoice(VehicleSale $vehicleSale): SymfonyResponse
    {
        $vehicleSale->load('payments');

        $pdf = Pdf::loadView('pdf.vehicle-sales.invoice', [
            'sale' => $vehicleSale,
            'currencySymbol' => Setting::get('currency_symbol', '₦'),
        ])->setPaper('a4', 'portrait');

        $customerSlug = Str::slug($vehicleSale->customer_name);
        $filename = 'Invoice-'.$vehicleSale->sale_number.($customerSlug ? '-'.$customerSlug : '').'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Download a specific Cash Receipt PDF for a particular payment installment.
     */
    public function downloadPaymentReceipt(VehicleSale $vehicleSale, VehicleSalePayment $payment): SymfonyResponse
    {
        $pdf = Pdf::loadView('pdf.vehicle-sales.receipt', [
            'sale' => $vehicleSale,
            'payment' => $payment,
            'currencySymbol' => Setting::get('currency_symbol', '₦'),
        ])->setPaper('a4', 'landscape');

        $customerSlug = Str::slug($vehicleSale->customer_name);
        $filename = 'Receipt-'.$payment->receipt_number.($customerSlug ? '-'.$customerSlug : '').'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Download the latest or primary Cash Receipt PDF for a vehicle sale.
     */
    public function downloadReceipt(VehicleSale $vehicleSale): SymfonyResponse
    {
        $latestPayment = $vehicleSale->payments()->latest('id')->first();

        if ($latestPayment) {
            return $this->downloadPaymentReceipt($vehicleSale, $latestPayment);
        }

        // Fallback if no payment record exists yet
        $dummyPayment = new VehicleSalePayment([
            'receipt_number' => $vehicleSale->sale_number,
            'amount_paid' => $vehicleSale->amount_paid,
            'payment_date' => $vehicleSale->sale_date,
            'payment_method' => $vehicleSale->payment_method ?? 'bank_transfer',
            'amount_in_words' => $vehicleSale->amount_in_words,
        ]);

        $pdf = Pdf::loadView('pdf.vehicle-sales.receipt', [
            'sale' => $vehicleSale,
            'payment' => $dummyPayment,
            'currencySymbol' => Setting::get('currency_symbol', '₦'),
        ])->setPaper('a4', 'landscape');

        $customerSlug = Str::slug($vehicleSale->customer_name);
        $filename = 'Receipt-'.$vehicleSale->sale_number.($customerSlug ? '-'.$customerSlug : '').'.pdf';

        return $pdf->download($filename);
    }
}
