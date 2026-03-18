<?php

namespace App\Http\Controllers;

use App\Events\MemoryChanged;
use App\Events\MemoryCommentCreated;
use App\Events\MemoryReactionCreated;
use App\Http\Requests\StoreMemoryCommentRequest;
use App\Http\Requests\StoreMemoryReactionRequest;
use App\Http\Requests\StoreMemoryRequest;
use App\Http\Requests\UpdateMemoryRequest;
use App\Http\Resources\MemoryCommentResource;
use App\Http\Resources\MemoryResource;
use App\Http\Resources\MemoryReactionResource;
use App\Models\Attachment;
use App\Models\Memory;
use App\Models\MemoryComment;
use App\Models\MemoryReaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Memory::query()
            ->with(['user', 'comments.user', 'reactions.user'])
            ->where(function (Builder $builder) use ($request): void {
                $builder->where('visibility', 'shared')
                    ->orWhere('user_id', $request->user()->id);
            })
            ->latest();

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        return MemoryResource::collection($query->get());
    }

    public function store(StoreMemoryRequest $request): MemoryResource
    {
        $validated = $request->validated();

        $memory = Memory::query()->create([
            ...$validated,
            'user_id' => $request->user()->id,
            'file_path' => $request->hasFile('file')
                ? $request->file('file')->store('memories', 'public')
                : null,
        ]);

        if ($request->hasFile('file')) {
            Attachment::query()->create([
                'memory_id' => $memory->id,
                'file_path' => $memory->file_path,
                'original_name' => $request->file('file')->getClientOriginalName(),
                'size' => $request->file('file')->getSize(),
            ]);
        }

        $memory->load(['user', 'comments.user', 'reactions.user']);
        event(new MemoryChanged($memory, 'created'));

        return new MemoryResource($memory);
    }

    public function update(UpdateMemoryRequest $request, Memory $memory): MemoryResource|JsonResponse
    {
        if ($memory->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validated();
        $memory->fill($validated);

        if ($request->hasFile('file')) {
            $memory->file_path = $request->file('file')->store('memories', 'public');
        }

        $memory->save();
        $memory->load(['user', 'comments.user', 'reactions.user']);
        event(new MemoryChanged($memory, 'updated'));

        return new MemoryResource($memory);
    }

    public function toggleVisibility(Memory $memory, Request $request): MemoryResource
    {
        abort_if($memory->user_id !== $request->user()->id, 403);

        $memory->visibility = $memory->visibility === 'private' ? 'shared' : 'private';
        $memory->save();
        $memory->load(['user', 'comments.user', 'reactions.user']);

        event(new MemoryChanged($memory, 'visibility_changed'));

        return new MemoryResource($memory);
    }

    public function comment(StoreMemoryCommentRequest $request, Memory $memory): MemoryCommentResource
    {
        $comment = MemoryComment::query()->create([
            'memory_id' => $memory->id,
            'user_id' => $request->user()->id,
            'comment' => $request->string('comment')->toString(),
        ]);

        $comment->load('user');
        event(new MemoryCommentCreated($comment));

        return new MemoryCommentResource($comment);
    }

    public function react(StoreMemoryReactionRequest $request, Memory $memory): MemoryReactionResource
    {
        $reaction = MemoryReaction::query()->updateOrCreate(
            [
                'memory_id' => $memory->id,
                'user_id' => $request->user()->id,
            ],
            [
                'reaction' => $request->string('reaction')->toString(),
            ]
        );

        $reaction->load('user');
        event(new MemoryReactionCreated($reaction));

        return new MemoryReactionResource($reaction);
    }
}
