<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return NotificationResource::collection(
            $request->user()->notifications()->latest()->paginate(20)
        );
    }

    public function markRead(int $notificationId, Request $request)
    {
        $notification = $request->user()->notifications()->findOrFail($notificationId);
        $notification->is_read = true;
        $notification->save();

        return new NotificationResource($notification);
    }
}
