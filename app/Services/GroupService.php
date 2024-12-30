<?php

namespace App\Services;

use App\Models\Conversation;
use Carbon\Carbon;

class GroupService
{
    /**
     * Check if a group exists for the same IP and today's date, and create if not.
     */
    public function createGroupIfNotExists($user)
    {
        $ipAddress = $user->profile->ip_address;


        $existingGroup = Conversation::whereHas('users', function ($query) use ($ipAddress) {
            $query->where('ip_address', $ipAddress);
        })
            ->whereDate('created_at', Carbon::today())
            ->where('is_group', 1)
            ->first();

        if (!$existingGroup) {
            $newGroup = Conversation::create([
                'is_group' => 1,
                'is_channel' => 0,
            ]);

            // Add user to the group
            $newGroup->users()->attach($user->id, ['ip_address' => $ipAddress]);

            return $newGroup;
        }
        if (!$existingGroup->users->contains($user->id)) {
            $existingGroup->users()->attach($user->id, ['ip_address' => $ipAddress]);
        }

        return $existingGroup;
    }
}
