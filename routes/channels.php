<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat', function ($user): bool {
    return (bool) $user;
});
