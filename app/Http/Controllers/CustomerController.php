<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Customer::class);

        $search = trim($request->input('search', ''));

        $query = Customer::withCount('orders')
            ->with(['orders.invoice']);

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                ->orWhere('phone', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%");
        }

        $customers = $query->orderBy('name')->paginate(12)->withQueryString();

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Customer::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['created_by'] = $request->user()->id;

        $customer = Customer::create($validated);

        ActivityLogService::log(
            'customer.created',
            "Created customer {$customer->name}",
            Customer::class,
            $customer->id
        );

        return back()->with('success', "Customer {$customer->name} created successfully.");
    }

    public function show(Customer $customer): Response
    {
        $this->authorize('view', $customer);

        $customer->load(['orders.invoice']);

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $customer->update($validated);

        ActivityLogService::log(
            'customer.updated',
            "Updated customer {$customer->name}",
            Customer::class,
            $customer->id
        );

        return back()->with('success', 'Customer details updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('delete', $customer);

        $name = $customer->name;
        $customer->delete();

        ActivityLogService::log(
            'customer.deleted',
            "Deleted customer {$name}",
            Customer::class,
            $customer->id
        );

        return redirect()->route('customers.index')
            ->with('success', "Customer {$name} deleted successfully.");
    }
}
