<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isLocked = $this->type === 'letter' && $this->unlock_at && Carbon::now()->lt($this->unlock_at);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'description' => $isLocked ? null : $this->description,
            'locked_message' => $isLocked ? sprintf('Locked until %s', $this->unlock_at?->toDateTimeString()) : null,
            'type' => $this->type,
            'memory_date' => $this->memory_date,
            'file_path' => $this->file_path,
            'visibility' => $this->visibility,
            'unlock_at' => $this->unlock_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ],
            'comments' => MemoryCommentResource::collection($this->whenLoaded('comments')),
            'reactions' => MemoryReactionResource::collection($this->whenLoaded('reactions')),
        ];
    }
}
