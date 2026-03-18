<?php

namespace App\Events;

use App\Models\MemoryComment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MemoryCommentCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public MemoryComment $comment)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('memories')];
    }

    public function broadcastAs(): string
    {
        return 'memory.comment.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->comment->id,
            'memory_id' => $this->comment->memory_id,
            'user_id' => $this->comment->user_id,
            'comment' => $this->comment->comment,
            'created_at' => $this->comment->created_at,
            'user' => [
                'id' => $this->comment->user?->id,
                'name' => $this->comment->user?->name,
            ],
        ];
    }
}
