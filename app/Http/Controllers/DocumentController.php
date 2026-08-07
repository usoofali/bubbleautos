<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Enums\TimelineEventType;
use App\Models\Document;
use App\Models\Order;
use App\Services\ActivityLogService;
use App\Services\TimelineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function store(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('create', Document::class);

        $validated = $request->validate([
            'document_type' => 'required|string',
            'title' => 'nullable|string|max:255',
            'file' => 'nullable|file|max:15360', // 15MB max
            'content' => 'nullable|string|max:10000',
        ]);

        if (empty($request->file('file')) && empty($validated['content'])) {
            return back()->withErrors(['file' => 'Please attach a document file or provide text content for Telex Release.']);
        }

        $docTypeEnum = DocumentType::tryFrom($validated['document_type']);
        $title = ! empty($validated['title'])
            ? $validated['title']
            : ($docTypeEnum ? $docTypeEnum->label() : ucwords(str_replace('_', ' ', $validated['document_type'])));

        $filePath = null;
        $fileName = null;
        $fileSize = 0;
        $mimeType = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('documents', 'local');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $mimeType = $file->getClientMimeType();
        }

        $doc = Document::create([
            'order_id' => $order->id,
            'title' => $title,
            'document_type' => $validated['document_type'],
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'content' => $validated['content'] ?? null,
            'uploaded_by' => $request->user()->id,
            'uploaded_at' => now(),
        ]);

        $typeLabel = $doc->document_type instanceof DocumentType ? $doc->document_type->label() : $doc->title;

        TimelineService::log(
            $order,
            TimelineEventType::DOCUMENT_UPLOADED,
            'Document Added',
            "Added document '{$title}' ({$typeLabel})."
        );

        ActivityLogService::log(
            'document.uploaded',
            "Uploaded document '{$title}' for order {$order->order_number}",
            Document::class,
            $doc->id
        );

        return back()->with('success', "Document '{$title}' added successfully.");
    }

    public function download(Document $document): StreamedResponse
    {
        $this->authorize('view', $document->order);

        if (! Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'Document file not found on server.');
        }

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        $title = $document->title;
        $order = $document->order;

        $document->delete(); // Model booted event permanently deletes file from disk!

        ActivityLogService::log(
            'document.deleted',
            "Deleted document '{$title}' from order {$order->order_number}",
            Document::class,
            $document->id
        );

        return back()->with('success', "Document '{$title}' deleted successfully.");
    }
}
