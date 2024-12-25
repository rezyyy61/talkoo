<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserProfile;
use Carbon\Carbon;
use App\Events\UserStatusUpdated;

class UpdateOfflineUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * php artisan users:offline
     */
    protected $signature = 'users:offline';

    /**
     * The console command description.
     */
    protected $description = 'Set users offline if they have been inactive for more than 30 seconds';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threshold = Carbon::now()->subSeconds(30);
        $offlineUsers = UserProfile::where('last_activity', '<', $threshold)
            ->where('is_online', true)
            ->get();

        foreach ($offlineUsers as $profile) {
            $profile->update(['is_online' => false]);
            event(new UserStatusUpdated($profile->user_id, false));
        }

        $this->info('Offline users updated successfully.');
    }
}
