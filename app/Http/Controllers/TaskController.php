<?php

namespace App\Http\Controllers;

use App\Events\TaskAssigned;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Notification;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $tasks = Task::query()
            ->with(['assignedUser', 'creator'])
            ->where(function ($query) use ($request): void {
                $query->where('assigned_to', $request->user()->id)
                    ->orWhere('created_by', $request->user()->id);
            })
            ->latest()
            ->get();

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request): TaskResource
    {
        $task = Task::query()->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        Notification::query()->create([
            'user_id' => $task->assigned_to,
            'type' => 'task',
            'data' => [
                'task_id' => $task->id,
                'title' => $task->title,
            ],
        ]);

        $task->load(['assignedUser', 'creator']);
        event(new TaskAssigned($task));

        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        abort_if($task->created_by !== $request->user()->id && $task->assigned_to !== $request->user()->id, 403);

        $task->update($request->validated());
        $task->load(['assignedUser', 'creator']);

        return new TaskResource($task);
    }

    public function destroy(Task $task, Request $request)
    {
        abort_if($task->created_by !== $request->user()->id, 403);

        $task->delete();

        return response()->json(['message' => 'Task deleted.']);
    }
}
