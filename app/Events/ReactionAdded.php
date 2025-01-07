<?php

namespace App\Events;

use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ReactionAdded implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $message;
    public $user;
    public $emoji;
    public $added;

    public function __construct(Message $message, User $user, $emoji, $added)
    {
        $this->message = $message;
        $this->user = $user;
        $this->emoji = $emoji;
        $this->added = $added;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('conversation.' . $this->message->conversation_id);
    }

    public function broadcastWith()
    {
        return [
            'message_id' => $this->message->id,
            'user_id' => $this->user->id,
            'emoji' => $this->emoji,
            'added' => $this->added,
        ];
    }
}
