<?php

namespace App\Events;

use App\Models\MemoryReaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MemoryReactionCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public MemoryReaction $reaction)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('memories')];
    }

    public function broadcastAs(): string
    {
        return 'memory.reaction.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->reaction->id,
            'memory_id' => $this->reaction->memory_id,
            'user_id' => $this->reaction->user_id,
            'reaction' => $this->reaction->reaction,
            'created_at' => $this->reaction->created_at,
            'user' => [
                'id' => $this->reaction->user?->id,
                'name' => $this->reaction->user?->name,
            ],
        ];
    }
}
