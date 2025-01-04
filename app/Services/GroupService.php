<?php

namespace App\Services;

use App\Models\Conversation;
use Carbon\Carbon;

class GroupService
{
    public function createOrJoinGroup($user)
    {
        $ipAddress = $user->profile->ip_address;

        $existingGroup = Conversation::where('is_group', 1)
            ->whereHas('users', function ($q) use ($ipAddress) {
                $q->where('ip_address', $ipAddress);
            })
            ->first();

        if (!$existingGroup) {
            $newGroup = Conversation::create([
                'is_group' => 1,
                'is_channel' => 0,
            ]);
            $newGroup->users()->attach($user->id, ['ip_address' => $ipAddress]);
            return $newGroup;
        } else {
            if (!$existingGroup->users->contains($user->id)) {
                $existingGroup->users()->attach($user->id, ['ip_address' => $ipAddress]);
            }
            return $existingGroup;
        }
    }

    public function removeUserFromGroup($user)
    {
        $ipAddress = $user->profile->ip_address;

        $existingGroup = Conversation::where('is_group', 1)
            ->whereHas('users', function ($q) use ($user, $ipAddress) {
                $q->where('user_id', $user->id)
                    ->where('ip_address', $ipAddress);
            })
            ->first();

        if ($existingGroup) {
            $existingGroup->users()->detach($user->id);
            if ($existingGroup->users()->count() === 0) {
                $existingGroup->delete();
            }
        }
    }

}
