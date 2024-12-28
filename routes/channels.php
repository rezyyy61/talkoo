<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Broadcast::channel('newRequest.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('updateRequest.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);
    if (!$conversation) {
        return false;
    }
    return $conversation->users->contains($user->id);
});

Broadcast::channel('sameIp.{ipEncoded}', function ($user, $ipEncoded) {

    $ip = str_replace('_', '.', $ipEncoded);

    if ($user->profile && $user->profile->ip_address === $ip) {
        return true;
    }
    return false;
});
