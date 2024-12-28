<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;



class UserStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $users_online;
    public $ipEncoded;

    public function __construct($users, $ipEncoded)
    {
        $this->users_online = $users;
        $this->ipEncoded = $ipEncoded;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('sameIp.' . $this->ipEncoded);
    }

}
