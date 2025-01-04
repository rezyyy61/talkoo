<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'is_online',
        'last_activity',
        'avatarImage',
        'avatarColor',
        'userId',
        'bio',
    ];

    protected $dates = [
        'last_activity',
        'created_at',
        'updated_at',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
