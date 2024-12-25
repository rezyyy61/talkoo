<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UpdateUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        try {
            if ($user = Auth::user()) {
                $profile = $user->profile;

                if (!$profile) {
                    $profile = $user->profile()->create([
                        'user_id' => $user->id,
                        'ip_address' => $request->ip(),
                        'is_online' => true,
                        'last_activity' => now(),
                        'avatar' => $this->getRandomAvatar(),
                    ]);
                }

                if ($profile) {
                    $profile->update([
                        'ip_address' => $request->ip(),
                        'is_online' => true,
                        'last_activity' => now(),
                    ]);

                    if (is_null($profile->avatar)) {
                        $profile->update(['avatar' => $this->getRandomAvatar()]);
                    }

                    // **Set the Redis cache key for online status**
                    Cache::put("user-{$user->id}-online", true, now()->addMinutes(1));

                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to update user activity", [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        return $next($request);
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
