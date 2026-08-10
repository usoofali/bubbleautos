<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Enums\ShipmentStatus;
use App\Models\Customer;
use App\Models\InvoiceItemTemplate;
use App\Models\Order;
use App\Models\Setting;
use App\Services\ActivityLogService;
use App\Services\OrderService;
use App\Services\VehicleApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Order::class);

        $search = trim($request->input('search', ''));
        $status = $request->input('status', '');

        $query = Order::with(['customer', 'invoice']);

        if ($search) {
            $query->globalSearch($search);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();
        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone']);

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'customers' => $customers,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'statusOptions' => array_map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ], ShipmentStatus::cases()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Order::class);

        $validated = $request->validate([
            'customer_id' => 'required_without:new_customer|nullable|exists:customers,id',
            'new_customer' => 'nullable|array',
            'new_customer.name' => 'required_with:new_customer|string|max:255',
            'new_customer.phone' => 'required_with:new_customer|string|max:50',
            'new_customer.email' => 'nullable|email|max:255',
            'vin' => 'required|string|size:17|unique:orders,vin',
            'auction_receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'make' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1900|max:'.(date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'shipping_line' => 'nullable|string|max:100',
            'destination' => 'nullable|string|max:100',
            'expected_arrival' => 'nullable|date',
        ]);

        if (empty($validated['customer_id']) && ! empty($validated['new_customer'])) {
            $customer = Customer::create([
                'name' => $validated['new_customer']['name'],
                'phone' => $validated['new_customer']['phone'],
                'email' => $validated['new_customer']['email'] ?? null,
                'created_by' => $request->user()->id,
            ]);
            $validated['customer_id'] = $customer->id;
        }

        unset($validated['new_customer']);

        $auctionReceiptFile = $request->file('auction_receipt');
        unset($validated['auction_receipt']);

        $order = $this->orderService->createOrder($validated);

        if ($auctionReceiptFile) {
            $path = $auctionReceiptFile->store('documents', 'local');
            $order->documents()->create([
                'title' => 'Auction Receipt',
                'document_type' => DocumentType::OTHER->value,
                'file_path' => $path,
                'file_name' => $auctionReceiptFile->getClientOriginalName(),
                'file_size' => $auctionReceiptFile->getSize(),
                'mime_type' => $auctionReceiptFile->getClientMimeType(),
                'uploaded_by' => $request->user()->id,
                'uploaded_at' => now(),
            ]);
        }

        return redirect()->route('orders.show', $order->id)
            ->with('success', "Order {$order->order_number} created successfully.");
    }

    public function show(Order $order): Response
    {
        $this->authorize('view', $order);

        $order->load([
            'customer',
            'invoice.items',
            'invoice.payments.recorder',
            'documents.uploader',
            'emails.attachments',
            'notes.user',
            'timelineEvents.user',
        ]);

        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone']);

        return Inertia::render('Orders/Show', [
            'order' => $order,
            'customers' => $customers,
            'companySettings' => [
                'name' => Setting::get('company_name', 'Bubbles Autos'),
                'logo' => Setting::get('company_logo', '/logo.jpeg'),
                'address' => Setting::get('contact_address', '100 Shipping Way, Houston, TX 77001'),
                'email' => Setting::get('contact_email', 'contact@bubbleautos.com'),
                'phone' => Setting::get('contact_phone', '+1 (800) 555-BUBBLE'),
                'currency_symbol' => Setting::get('currency_symbol', '$'),
                'currency_code' => Setting::get('currency_code', 'USD'),
            ],
            'statusOptions' => array_map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ], ShipmentStatus::cases()),
            'invoiceItemTemplates' => InvoiceItemTemplate::orderBy('description')->get(['id', 'description', 'default_amount']),
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vin' => 'required|string|size:17|unique:orders,vin,'.$order->id,
            'make' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1900|max:'.(date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'shipping_line' => 'nullable|string|max:100',
            'destination' => 'nullable|string|max:100',
            'expected_arrival' => 'nullable|date',
        ]);

        $this->orderService->updateOrder($order, $validated);

        return back()->with('success', 'Order information updated successfully.');
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'status' => 'required|string',
            'notes' => 'nullable|string|max:500',
        ]);

        $this->orderService->updateStatus($order, $validated['status'], $validated['notes'] ?? null);

        return back()->with('success', 'Shipment status updated successfully.');
    }

    public function updateTracking(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'expected_arrival' => 'nullable|date',
            'status' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
        ]);

        if (array_key_exists('expected_arrival', $validated)) {
            $order->update(['expected_arrival' => $validated['expected_arrival']]);
        }

        if (! empty($validated['status'])) {
            $this->orderService->updateStatus($order, $validated['status'], $validated['notes'] ?? 'Status updated during tracking update.');
        }

        return back()->with('success', 'Tracking information updated successfully.');
    }

    public function syncVehicleApi(Order $order, VehicleApiService $vehicleApiService): RedirectResponse
    {
        $this->authorize('update', $order);

        $result = $this->orderService->syncVehicleApiData($order, $vehicleApiService);

        if ($result['success']) {
            $photoCount = count($result['pictures']);

            return back()->with('success', "Vehicle specs & photos synced successfully ({$photoCount} photos).");
        }

        return back()->with('error', $result['message'] ?? 'Unable to resolve vehicle data for this VIN.');
    }

    public function lookupVin(string $vin, VehicleApiService $vehicleApiService)
    {
        $this->authorize('viewAny', Order::class);

        return response()->json($vehicleApiService->lookupByVin($vin));
    }

    public function destroy(Order $order): RedirectResponse
    {
        $this->authorize('delete', $order);

        $orderNumber = $order->order_number;
        $order->delete();

        ActivityLogService::log(
            'order.deleted',
            "Deleted order {$orderNumber}",
            Order::class,
            $order->id
        );

        return redirect()->route('orders.index')
            ->with('success', "Order {$orderNumber} deleted successfully.");
    }
}
