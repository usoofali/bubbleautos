<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Enums\EmailStatus;
use App\Enums\TimelineEventType;
use App\Models\Document;
use App\Models\Email;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmailProcessingService
{
    /**
     * Process an incoming raw/simulated email.
     */
    public function processIncomingEmail(array $emailData): Email
    {
        return DB::transaction(function () use ($emailData) {
            $subject = $emailData['subject'] ?? '';
            $body = $emailData['body'] ?? '';
            $attachments = $emailData['attachments'] ?? [];

            // 17-character VIN pattern (standard VIN alphanumeric excluding I, O, Q)
            $vinPattern = '/[A-HJ-NPR-Z0-9]{17}/i';

            $extractedVin = null;

            if (preg_match($vinPattern, $subject, $matches)) {
                $extractedVin = strtoupper($matches[0]);
            } elseif (preg_match($vinPattern, $body, $matches)) {
                $extractedVin = strtoupper($matches[0]);
            } else {
                foreach ($attachments as $att) {
                    $filename = is_array($att) ? ($att['filename'] ?? '') : $att;
                    if (preg_match($vinPattern, $filename, $matches)) {
                        $extractedVin = strtoupper($matches[0]);
                        break;
                    }
                }
            }

            $matchedOrder = null;
            if ($extractedVin) {
                $matchedOrder = Order::where('vin', $extractedVin)->first();
            }

            $email = Email::create([
                'order_id' => $matchedOrder?->id,
                'message_id' => $emailData['message_id'] ?? 'MSG-'.uniqid(),
                'sender' => $emailData['sender'],
                'recipient' => $emailData['recipient'] ?? 'shipping@bubbleautos.com',
                'subject' => $subject,
                'body' => $body,
                'attachments_count' => count($attachments),
                'processing_status' => $matchedOrder ? EmailStatus::MATCHED : EmailStatus::NEEDS_REVIEW,
                'received_at' => $emailData['received_at'] ?? now(),
            ]);

            // Save attachments
            foreach ($attachments as $att) {
                $filename = is_array($att) ? ($att['filename'] ?? 'attachment.pdf') : $att;
                $filePath = is_array($att) ? ($att['file_path'] ?? 'attachments/sample_'.uniqid().'.pdf') : 'attachments/sample_'.uniqid().'.pdf';

                if (! Storage::disk('local')->exists($filePath)) {
                    Storage::disk('local')->put($filePath, "Bubbles Autos Attachment File: {$filename}\nProcessed for Email ID #{$email->id}\nDate: ".now());
                }

                $email->attachments()->create([
                    'filename' => $filename,
                    'file_path' => $filePath,
                    'file_size' => is_array($att) ? ($att['file_size'] ?? 102400) : 102400,
                    'mime_type' => is_array($att) ? ($att['mime_type'] ?? 'application/pdf') : 'application/pdf',
                ]);
            }

            if ($matchedOrder) {
                TimelineService::log(
                    $matchedOrder,
                    TimelineEventType::EMAIL_RECEIVED,
                    'Shipping Email Auto-Matched',
                    "Received email from {$email->sender} with subject '{$email->subject}' automatically linked via VIN {$extractedVin}."
                );

                ActivityLogService::log(
                    'email.auto_matched',
                    "Auto-matched incoming email '{$email->subject}' to order {$matchedOrder->order_number}",
                    Email::class,
                    $email->id
                );
            } else {
                ActivityLogService::log(
                    'email.needs_review',
                    "Incoming email '{$email->subject}' requires manual VIN review",
                    Email::class,
                    $email->id
                );
            }

            return $email;
        });
    }

    public function linkToOrder(Email $email, Order $order, array $attachmentDocumentTypes = []): Email
    {
        return DB::transaction(function () use ($email, $order, $attachmentDocumentTypes) {
            $email->update([
                'order_id' => $order->id,
                'processing_status' => EmailStatus::MATCHED,
            ]);

            $importedDocsCount = 0;

            if (! empty($attachmentDocumentTypes)) {
                $attachments = $email->attachments()->whereIn('id', array_keys($attachmentDocumentTypes))->get();

                foreach ($attachments as $attachment) {
                    $docTypeValue = $attachmentDocumentTypes[$attachment->id] ?? null;

                    if ($docTypeValue && $docTypeValue !== 'skip' && $docTypeValue !== 'none') {
                        $docTypeEnum = DocumentType::tryFrom($docTypeValue);

                        if ($docTypeEnum) {
                            Document::create([
                                'order_id' => $order->id,
                                'title' => $attachment->filename,
                                'document_type' => $docTypeEnum,
                                'file_path' => $attachment->file_path,
                                'file_name' => $attachment->filename,
                                'file_size' => $attachment->file_size,
                                'mime_type' => $attachment->mime_type,
                                'uploaded_by' => auth()->id(),
                                'uploaded_at' => now(),
                            ]);
                            $importedDocsCount++;
                        }
                    }
                }
            }

            $logDescription = "Email '{$email->subject}' from {$email->sender} manually assigned to order by staff.";
            if ($importedDocsCount > 0) {
                $logDescription .= " Imported {$importedDocsCount} attachment(s) into Order Documents.";
            }

            TimelineService::log(
                $order,
                TimelineEventType::EMAIL_RECEIVED,
                'Email Manually Linked',
                $logDescription
            );

            ActivityLogService::log(
                'email.manually_linked',
                "Manually linked email '{$email->subject}' to order {$order->order_number} ({$importedDocsCount} document(s) imported)",
                Email::class,
                $email->id
            );

            return $email;
        });
    }
}
