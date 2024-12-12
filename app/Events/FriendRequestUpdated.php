<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FriendRequestUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $friendship;
    public $status;

    public function __construct($friendship, $status)
    {
        if (!$friendship) {
            Log::error('Friendship object is null in FriendRequestUpdated event.');
        }
        $this->friendship = $friendship;
        $this->status = $status;

        if ($friendship->user) {
            $this->friendship->sender_name = $friendship->user->name;
            $this->friendship->sender_email = $friendship->user->email;
        } else {
            Log::error('User relationship is null for Friendship ID: ' . $friendship->id);
        }
    }


    public function broadcastOn()
    {
        return new PrivateChannel('updateRequest.' . $this->friendship->user_id);
    }

    public function broadcastWith()
    {
        Log::info('Broadcasting event', [
            'channel' => 'updateRequest.' . $this->friendship->user_id,
            'friendship' => $this->friendship,
            'status' => $this->status,
        ]);

        return [
            'message' => ucwords("{$this->friendship->sender_name}. {$this->status} Your friend request."),
            'friendship' => [
                'id' => $this->friendship->id,
                'user_id' => $this->friendship->user_id,
                'friend_id' => $this->friendship->friend_id,
                'status' => $this->status,
                'sender_name' => ucwords($this->friendship->sender_name,),
                'sender_email' => $this->friendship->sender_email,
            ],
        ];
    }

    public function broadcastAs()
    {
        return 'FriendRequestUpdated';
    }
}
