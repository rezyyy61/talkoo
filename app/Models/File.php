<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class File extends Model
{
    protected $fillable = ['message_id', 'file_path', 'file_type', 'file_size', 'file_name'];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
