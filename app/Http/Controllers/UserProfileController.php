<?php

namespace App\Http\Controllers;

use App\Events\UserStatusUpdated;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

class UserProfileController extends Controller
{
    public function heartbeat(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $profile = $user->profile;
            if ($profile) {
                $profile->update([
                    'is_online' => true,
                    'last_activity' => now(),
                    'ip_address' => $request->ip(),
                ]);
            } else {
                Auth::user()->profile()->create([
                    'user_id' => Auth::id(),
                    'last_activity' => now(),
                    'is_online' => true,
                    'ip_address' => $request->ip(),
                    'avatar' => $this->getRandomAvatar(),
                ]);
            }

            broadcast(new UserStatusUpdated(Auth::id(), true));
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'unauthorized'], 401);
    }

    private function getRandomAvatar()
    {
        $avatars = [
            '#FF5733', // Vibrant Orange
            '#33FF57', // Fresh Green
            '#3357FF', // Bright Blue
            '#FF33A8', // Hot Pink
            '#FF8F33', // Sunset Orange
            '#33FFF5', // Aqua Blue
            '#8D33FF', // Purple Haze
            '#FF3333', // Red Passion
            '#33FFB5', // Mint Green
            '#FFC733', // Golden Yellow
        ];

        return $avatars[array_rand($avatars)];
    }
}
