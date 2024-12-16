<?php

namespace App\Events;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class MessageRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageIds;
    public $readIds;
    public $conversationId;

    public function __construct(array $messageIds, $readIds, $conversationId)
    {
        $this->messageIds = $messageIds;
        $this->readIds = $readIds;
        $this->conversationId = $conversationId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('conversation.'.$this->conversationId);
    }

    public function broadcastWith()
    {
        return [
            'message_Ids' => $this->messageIds,
            'read_Id' => $this->readIds,
        ];
    }
}
