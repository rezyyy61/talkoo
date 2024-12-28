<?php

namespace App\Http\Middleware;

use App\Events\UserStatusUpdated;
use App\Models\UserProfile;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                        'avatar' => '#f3f4f6',
                    ]);
                }

                if ($profile) {
                    $profile->update([
                        'ip_address' => $request->ip(),
                        'is_online' => true,
                        'last_activity' => now(),
                    ]);

                    if (is_null($profile->avatar)) {
                        $profile->update(['avatar' => '#f3f4f6']);
                    }

                    $sameIpUsers = UserProfile::where('ip_address', $profile->ip_address)
                        ->where('is_online', true)
                        ->with('user:id,name')
                        ->get();
                    $ipEncoded = str_replace('.', '_', $profile->ip_address);
                    broadcast(new UserStatusUpdated($sameIpUsers, $ipEncoded))->toOthers();
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

}
