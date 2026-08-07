<?php

namespace App\Http\Controllers;

use App\Enums\EmailStatus;
use App\Enums\InvoiceStatus;
use App\Enums\ShipmentStatus;
use App\Models\Document;
use App\Models\Email;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\TimelineEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Metric Widgets
        $ordersInTransit = Order::where('status', ShipmentStatus::IN_TRANSIT)->count();
        $deliveredOrders = Order::where('status', ShipmentStatus::DELIVERED)->count();

        $outstandingInvoicesCount = Invoice::where('status', '!=', InvoiceStatus::PAID)->count();
        $outstandingInvoicesTotal = Invoice::where('status', '!=', InvoiceStatus::PAID)->sum('balance');

        $pendingDocuments = Document::whereNull('file_path')->orWhere('file_path', '')->count();
        $emailsToReview = Email::where('processing_status', EmailStatus::NEEDS_REVIEW)->count();

        // Recent Timeline Stream
        $recentTimeline = TimelineEvent::with(['order', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        // Recent Orders
        $recentOrders = Order::with(['customer', 'invoice'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'metrics' => [
                'orders_in_transit' => $ordersInTransit,
                'delivered_orders' => $deliveredOrders,
                'outstanding_invoices_count' => $outstandingInvoicesCount,
                'outstanding_invoices_total' => (float) $outstandingInvoicesTotal,
                'emails_to_review' => $emailsToReview,
            ],
            'recentTimeline' => $recentTimeline,
            'recentOrders' => $recentOrders,
        ]);
    }
}
