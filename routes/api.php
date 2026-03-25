<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MemoryController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::options('/{any}', function () {
    return response()->json([], 200);
})->where('any', '.*');
Route::get('/env-check', function () {
    return [
        'app_key' => config('app.key'),
        'app_env' => config('app.env'),
        'debug' => config('app.debug'),
    ];
});
Route::get('/debug', function () {
    try {
        return 'Laravel working';
    } catch (\Throwable $e) {
        return $e->getMessage();
    }
});
Route::get('/logs', function () {
    return response()->file(storage_path('logs/laravel.log'));
});

Route::get('/test', function () {
    return response()->json(['ok' => true]);
});

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/settings/password', [AuthController::class, 'updatePassword']);
    Route::post('/settings/email', [AuthController::class, 'updateEmail']);

    Route::get('/users', [UserController::class, 'index']);

    Route::get('/notes', [NoteController::class, 'index']);
    Route::post('/notes', [NoteController::class, 'store']);
    Route::put('/notes/{note}', [NoteController::class, 'update']);
    Route::delete('/notes/{note}', [NoteController::class, 'destroy']);

    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::patch('/messages/{message}/seen', [MessageController::class, 'markSeen']);
    Route::delete('/messages/{message}', [MessageController::class, 'destroy']);

    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notificationId}/read', [NotificationController::class, 'markRead']);

    Route::get('/memories', [MemoryController::class, 'index']);
    Route::post('/memories', [MemoryController::class, 'store']);
    Route::put('/memories/{memory}', [MemoryController::class, 'update']);
    Route::patch('/memories/{memory}/visibility', [MemoryController::class, 'toggleVisibility']);
    Route::post('/memories/{memory}/comments', [MemoryController::class, 'comment']);
    Route::post('/memories/{memory}/reactions', [MemoryController::class, 'react']);
});
