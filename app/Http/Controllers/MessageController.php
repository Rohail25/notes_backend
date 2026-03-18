<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Attachment;
use App\Models\Message;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $authId = $request->user()->id;
        $otherUserId = (int) $request->input('user_id');

        $messages = Message::query()
            ->with(['sender', 'receiver'])
            ->where(function ($query) use ($authId, $otherUserId): void {
                $query->where('sender_id', $authId)
                    ->where('receiver_id', $otherUserId);
            })
            ->orWhere(function ($query) use ($authId, $otherUserId): void {
                $query->where('sender_id', $otherUserId)
                    ->where('receiver_id', $authId);
            })
            ->orderBy('created_at')
            ->get();

        return MessageResource::collection($messages);
    }

    public function store(StoreMessageRequest $request): MessageResource
    {
        $validated = $request->validated();

        $filePath = null;

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('chat', 'public');
        }

        $message = Message::query()->create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $validated['receiver_id'],
            'message' => $validated['message'] ?? null,
            'file_path' => $filePath,
        ]);

        if ($filePath && $request->hasFile('file')) {
            Attachment::query()->create([
                'message_id' => $message->id,
                'file_path' => $filePath,
                'original_name' => $request->file('file')->getClientOriginalName(),
                'size' => $request->file('file')->getSize(),
            ]);
        }

        Notification::query()->create([
            'user_id' => $validated['receiver_id'],
            'type' => 'message',
            'data' => [
                'message_id' => $message->id,
                'sender_id' => $request->user()->id,
            ],
        ]);

        $message->load(['sender', 'receiver']);
        broadcast(new MessageSent($message))->toOthers();

        return new MessageResource($message);
    }

    public function markSeen(Message $message, Request $request): MessageResource
    {
        abort_if($message->receiver_id !== $request->user()->id, 403);

        if (! $message->seen_at) {
            $message->seen_at = Carbon::now();
            $message->save();
        }

        $message->load(['sender', 'receiver']);

        return new MessageResource($message);
    }

    public function destroy(Message $message, Request $request)
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $message->delete();

        return response()->json(['message' => 'Message deleted by admin.']);
    }
}
