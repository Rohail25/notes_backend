<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Models\Note;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Note::query();

        // Admin sees all notes, users see only their own
        if ($request->user()->role !== 'admin') {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return NoteResource::collection($query->latest()->paginate(15));
    }

    public function store(StoreNoteRequest $request): NoteResource
    {
        $note = Note::query()->create([
            'user_id' => $request->user()->id,
            ...$request->validated(),
        ]);

        $this->notifyAdmin($note, 'created');

        return new NoteResource($note);
    }

    public function update(UpdateNoteRequest $request, Note $note): NoteResource|JsonResponse
    {
        // Only allow the owner or admin to update the note
        if ($note->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $note->update($request->validated());
        $this->notifyAdmin($note, 'updated');

        return new NoteResource($note);
    }

    public function destroy(Request $request, Note $note): JsonResponse
    {
        // Only admin can delete notes
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Only admin can delete notes'], 403);
        }

        $note->delete();

        return response()->json(['message' => 'Note deleted successfully']);
    }

    protected function notifyAdmin(Note $note, string $action): void
    {
        $admin = User::query()->where('role', 'admin')->first();

        if (! $admin) {
            return;
        }

        Notification::query()->create([
            'user_id' => $admin->id,
            'type' => 'note',
            'data' => [
                'note_id' => $note->id,
                'action' => $action,
                'title' => $note->title,
                'user_id' => $note->user_id,
            ],
        ]);
    }
}
