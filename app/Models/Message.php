<?php

namespace App\Models;

use App\Enums\MessageStatus;
use App\Enums\MessageType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    const TYPE_TEXT = 'text';
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_AUDIO = 'audio';
    const TYPE_PDF = 'pdf';
    const TYPE_FILE = 'file';
    const TYPE_EMOJI = 'emoji';
    const TYPE_MIXED = 'mixed';

    const STATUS_SENT = 'sent';
    const STATUS_RECEIVED = 'received';
    const STATUS_READ = 'read';
    protected $fillable = ['conversation_id', 'reply_to_message_id', 'sender_id', 'message_type', 'content', 'status'];

    protected $casts = [
        'message_type' => MessageType::class,
        'status' => MessageStatus::class,
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function replyToMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_message_id');
    }

}
