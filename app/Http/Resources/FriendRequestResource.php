<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FriendRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'friend_id'     => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'status'        => 'pending',
            'requested_at'  => $this->pivot->created_at->toDateTimeString(),
        ];
    }
}
