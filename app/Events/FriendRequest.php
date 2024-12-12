<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FriendRequest implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The friendship model instance.
     */
    public $friendship;

    /**
     * Create a new event instance.
     */
    public function __construct($friendship)
    {
        $this->friendship = $friendship;

        // Add sender's name and email
        $this->friendship->sender_name = $friendship->user->name;
        $this->friendship->sender_email = $friendship->user->email;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('newRequest.' . $this->friendship->friend_id)
        ];
    }

    public function broadcastWith()
    {
        return [
            'message' => 'You have a friend request',
            'friendship' => [
                'id' => $this->friendship->id,
                'user_id' => $this->friendship->user_id,
                'friend_id' => $this->friendship->friend_id,
                'status' => $this->friendship->status,
                'sender_name' => $this->friendship->sender_name,
                'sender_email' => $this->friendship->sender_email,
            ],
        ];
    }
}
