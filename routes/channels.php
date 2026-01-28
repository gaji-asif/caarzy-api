<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', function ($user, $id) {
    Log::info('Authorizing channel', ['user' => $user, 'id' => $id]);
    // Log authorization attempts for debugging
    \Log::info('🔐 Broadcast auth check', [
        'auth_user_id' => optional($user)->id,
        'requested_channel_id' => $id,
    ]);

    // Authorize only if the current authenticated user
    // is the same as the channel {id} they are subscribing to
    return (int) $user->id === (int) $id;
});
