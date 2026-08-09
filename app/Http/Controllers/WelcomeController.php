<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    public function __invoke(): Response
    {
        $settings = Setting::where('group', 'website')->get()->pluck('value', 'key')->toArray();
        $general = Setting::where('group', 'general')->get()->pluck('value', 'key')->toArray();

        return Inertia::render('Welcome', [
            'cms' => array_merge([
                'company_name' => $general['company_name'] ?? 'Bubbles Autos',
                'company_logo' => $general['company_logo'] ?? '/images/logo.png',
                'hero_title' => 'Global Vehicle Shipment & Management System',
                'hero_subtitle' => 'Internal management portal for Bubble Autos staff to manage customer vehicle orders, VIN search, documents, invoices, and shipment tracking.',
                'contact_phone' => '+1 (800) 555-BUBBLE',
                'contact_email' => 'contact@bubbleautos.com',
                'contact_address' => '100 Shipping Way, Houston, TX 77001',
            ], $settings),
        ]);
    }

    public function lookupVin(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 6) {
            return response()->json([
                'found' => false,
                'message' => 'Please enter at least 6 characters of the VIN to search.',
            ]);
        }

        $order = Order::with('documents')
            ->where('vin', 'LIKE', "%{$q}%")
            ->orWhere('order_number', 'LIKE', "%{$q}%")
            ->latest()
            ->first();

        if (! $order) {
            return response()->json([
                'found' => false,
                'message' => "No vehicle order matching '{$q}' found in system records.",
            ]);
        }

        $documents = $order->documents;
        $docTypes = $documents->pluck('document_type')->map(fn ($type) => is_object($type) && isset($type->value) ? $type->value : (string) $type)->toArray();

        if (in_array('dock_receipt', $docTypes)) {
            $titleStatus = 'Dock Receipt Attached & Vaulted';
        } elseif (in_array('title', $docTypes)) {
            $titleStatus = 'Vehicle Title Verified in Vault';
        } elseif (in_array('bill_of_lading', $docTypes) || in_array('telex_release', $docTypes)) {
            $titleStatus = 'Bill of Lading / Release Vaulted';
        } elseif ($documents->count() > 0) {
            $titleStatus = "{$documents->count()} Document(s) Verified in Vault";
        } else {
            $titleStatus = 'Pending Dock Receipt / Title Vaulting';
        }

        return response()->json([
            'found' => true,
            'result' => [
                'vin' => $order->vin,
                'orderNo' => $order->order_number,
                'vehicle' => trim("{$order->year} {$order->make} {$order->model}"),
                'status' => $order->status ? $order->status->label() : 'Pending',
                'eta' => $order->expected_arrival ? $order->expected_arrival->format('M d, Y') : 'Pending ETA',
                'location' => trim("{$order->shipping_line} -> {$order->destination}", ' ->'),
                'titleStatus' => $titleStatus,
                'pictures' => $this->normalizePictures($order->pictures),
            ],
        ]);
    }

    private function normalizePictures(mixed $pictures): array
    {
        if (empty($pictures)) {
            return [];
        }

        if (is_string($pictures)) {
            $decoded = json_decode($pictures, true);
            $pictures = is_array($decoded) ? $decoded : explode(',', $pictures);
        }

        if (! is_array($pictures)) {
            return [];
        }

        $urls = [];
        foreach ($pictures as $item) {
            if (is_string($item) && ! empty(trim($item))) {
                $urls[] = trim($item);
            } elseif (is_array($item)) {
                $url = $item['url'] ?? $item['src'] ?? $item['path'] ?? $item['file_path'] ?? null;
                if ($url && is_string($url) && ! empty(trim($url))) {
                    $urls[] = trim($url);
                }
            }
        }

        return array_values(array_unique($urls));
    }
}
