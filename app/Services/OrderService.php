<?php

namespace App\Services;

use App\Enums\ShipmentStatus;
use App\Enums\TimelineEventType;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(array $data): Order
    {
        $order = DB::transaction(function () use ($data) {
            $data['order_number'] = Order::generateNextOrderNumber();
            $data['status'] = $data['status'] ?? ShipmentStatus::PENDING->value;

            $order = Order::create($data);

            // Auto-create initial invoice with total = 0
            $invoicePrefix = Setting::get('invoice_prefix', 'INV-');
            $currency = Setting::get('currency_symbol', '$');

            Invoice::create([
                'order_id' => $order->id,
                'invoice_number' => sprintf('%s%05d', $invoicePrefix, $order->id),
                'subtotal' => 0,
                'discount' => 0,
                'total' => 0,
                'paid' => 0,
                'balance' => 0,
                'currency' => $currency,
                'status' => 'unpaid',
            ]);

            TimelineService::log(
                $order,
                TimelineEventType::ORDER_CREATED,
                'Order Created',
                "Order {$order->order_number} registered for VIN {$order->vin}."
            );

            ActivityLogService::log(
                'order.created',
                "Created order {$order->order_number} for VIN {$order->vin}",
                Order::class,
                $order->id
            );

            return $order;
        });

        // Attempt background vehicle API lookup for photos & specs
        try {
            $this->syncVehicleApiData($order, app(VehicleApiService::class));
        } catch (\Throwable $e) {
            // Ignore API lookup errors during initial order creation
        }

        return $order->fresh();
    }

    public function syncVehicleApiData(Order $order, VehicleApiService $vehicleApiService): array
    {
        $result = $vehicleApiService->lookupByVin($order->vin);

        if ($result['success']) {
            $updates = [];

            if (! empty($result['make'])) {
                $updates['make'] = $result['make'];
            }
            if (! empty($result['model'])) {
                $updates['model'] = $result['model'];
            }
            if (! empty($result['year'])) {
                $updates['year'] = $result['year'];
            }
            if (! empty($result['pictures'])) {
                $updates['pictures'] = $result['pictures'];
            }

            if (! empty($updates)) {
                $order->update($updates);

                TimelineService::log(
                    $order,
                    TimelineEventType::MANUAL_UPDATE,
                    'Vehicle Specs & Photos Synced',
                    "Synced vehicle specs from API: {$order->year} {$order->make} {$order->model} (".count($result['pictures']).' photos).'
                );
            }
        }

        return $result;
    }

    public function updateStatus(Order $order, string $newStatus, ?string $notes = null): Order
    {
        $oldStatus = $order->status->label();
        $order->update(['status' => $newStatus]);
        $order->refresh();

        $statusLabel = $order->status->label();

        TimelineService::log(
            $order,
            TimelineEventType::SHIPMENT_STATUS_UPDATED,
            'Shipment Status Updated',
            "Status changed from '{$oldStatus}' to '{$statusLabel}'.".($notes ? " Notes: {$notes}" : '')
        );

        ActivityLogService::log(
            'order.status_updated',
            "Updated status for {$order->order_number} to {$statusLabel}",
            Order::class,
            $order->id
        );

        return $order;
    }

    public function updateOrder(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            $order->update($data);

            TimelineService::log(
                $order,
                TimelineEventType::MANUAL_UPDATE,
                'Order Details Updated',
                "Updated information for Order {$order->order_number}."
            );

            ActivityLogService::log(
                'order.updated',
                "Updated order details for {$order->order_number}",
                Order::class,
                $order->id
            );

            return $order;
        });
    }
}
