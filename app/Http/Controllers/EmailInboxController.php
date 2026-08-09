<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Enums\EmailStatus;
use App\Enums\ShipmentStatus;
use App\Models\Email;
use App\Models\EmailAttachment;
use App\Models\Order;
use App\Services\EmailFetchService;
use App\Services\EmailProcessingService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EmailInboxController extends Controller
{
    public function __construct(protected EmailProcessingService $emailProcessingService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Order::class);

        $status = $request->input('status', 'needs_review');
        $search = trim($request->input('search', ''));

        $query = Email::with(['order', 'attachments']);

        if ($status) {
            $query->where('processing_status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'LIKE', "%{$search}%")
                    ->orWhere('sender', 'LIKE', "%{$search}%")
                    ->orWhere('body', 'LIKE', "%{$search}%");
            });
        }

        $emails = $query->orderBy('received_at', 'desc')->paginate(10)->withQueryString();
        $orders = Order::orderBy('order_number', 'desc')->get(['id', 'order_number', 'vin', 'make', 'model']);

        return Inertia::render('Emails/Index', [
            'emails' => $emails,
            'orders' => $orders,
            'statusOptions' => array_map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ], ShipmentStatus::cases()),
            'documentTypeOptions' => array_map(fn ($d) => [
                'value' => $d->value,
                'label' => $d->label(),
            ], DocumentType::cases()),
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
        ]);
    }

    public function linkOrder(Request $request, Email $email): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'status' => 'nullable|string',
            'attachment_document_types' => 'nullable|array',
            'attachment_document_types.*' => 'nullable|string',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        $this->authorize('update', $order);

        $this->emailProcessingService->linkToOrder(
            $email,
            $order,
            $validated['attachment_document_types'] ?? []
        );

        if (! empty($validated['status'])) {
            app(OrderService::class)->updateStatus(
                $order,
                $validated['status'],
                "Status updated during email linking ('{$email->subject}')."
            );
        }

        return back()->with('success', "Email linked to Order {$order->order_number} successfully.");
    }

    public function unlinkOrder(Email $email): RedirectResponse
    {
        $this->authorize('viewAny', Order::class);

        $email->update([
            'order_id' => null,
            'processing_status' => EmailStatus::NEEDS_REVIEW,
        ]);

        return back()->with('success', 'Email unlinked from order successfully.');
    }

    public function fetchFromImap(EmailFetchService $emailFetchService): RedirectResponse
    {
        $this->authorize('viewAny', Order::class);

        $result = $emailFetchService->fetchLatestEmails();

        if (! empty($result['message']) && (str_contains($result['message'], 'Failed') || str_contains($result['message'], 'Error'))) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message'] ?? "Fetched and processed {$result['count']} incoming email(s) for {$result['target_email']}.");
    }

    public function downloadAttachment(EmailAttachment $attachment)
    {
        $this->authorize('viewAny', Order::class);

        if ($attachment->file_path && Storage::disk('local')->exists($attachment->file_path)) {
            return Storage::disk('local')->download($attachment->file_path, $attachment->filename);
        }

        return response()->streamDownload(function () use ($attachment) {
            echo "Bubbles Autos Attachment Sample Content for file: {$attachment->filename}\nAssociated with Email ID #{$attachment->email_id}.";
        }, $attachment->filename, [
            'Content-Type' => $attachment->mime_type ?? 'application/pdf',
        ]);
    }
}
