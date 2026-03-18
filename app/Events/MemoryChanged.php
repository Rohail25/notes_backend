<?php

namespace App\Events;

use App\Models\Memory;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MemoryChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Memory $memory, public string $action)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('memories')];
    }

    public function broadcastAs(): string
    {
        return 'memory.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->memory->id,
            'action' => $this->action,
            'user_id' => $this->memory->user_id,
            'title' => $this->memory->title,
            'description' => $this->memory->description,
            'type' => $this->memory->type,
            'memory_date' => $this->memory->memory_date,
            'visibility' => $this->memory->visibility,
            'unlock_at' => $this->memory->unlock_at,
            'created_at' => $this->memory->created_at,
            'updated_at' => $this->memory->updated_at,
        ];
    }
}
