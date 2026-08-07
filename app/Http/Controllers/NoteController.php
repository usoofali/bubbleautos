<?php

namespace App\Http\Controllers;

use App\Enums\TimelineEventType;
use App\Models\Note;
use App\Models\Order;
use App\Services\ActivityLogService;
use App\Services\TimelineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function store(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $note = Note::create([
            'order_id' => $order->id,
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);

        TimelineService::log(
            $order,
            TimelineEventType::NOTE_ADDED,
            'Staff Note Added',
            "{$request->user()->name}: \"{$note->content}\""
        );

        ActivityLogService::log(
            'note.created',
            "Added internal note to order {$order->order_number}",
            Note::class,
            $note->id
        );

        return back()->with('success', 'Staff note posted successfully.');
    }
}
