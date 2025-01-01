<?php

namespace App\Services;

use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\File;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessageService
{
    /**
     * Find or create a private conversation between two users
     */
    public function findOrCreatePrivateConversation($senderId, $receiverId): Conversation
    {
        $conversation = Conversation::where('is_group', false)
            ->where('is_channel', false)
            ->whereHas('users', function ($q) use ($senderId, $receiverId) {
                $q->where('user_id', $senderId)
                    ->orWhere('user_id', $receiverId);
            }, '=', 2)
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'is_group'   => false,
                'is_channel' => false,
            ]);
            $conversation->users()->attach([$senderId, $receiverId], ['joined_at' => now()]);
        }

        return $conversation;
    }

    /**
     * Return the desired group conversation
     */
    public function getGroupConversation($conversationId): Conversation
    {
        return Conversation::where('id', $conversationId)
            ->where('is_group', true)
            ->firstOrFail();
    }

    /**
     * Return the conversation to the desired channel
     */
    public function getChannelConversation($conversationId): Conversation
    {
        return Conversation::where('id', $conversationId)
            ->where('is_channel', true)
            ->firstOrFail();
    }

    /**
     * Send a text message
     */
    public function sendTextMessage(Conversation $conversation, User $sender, $text): Message
    {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $sender->id,
            'message_type'    => MessageType::TEXT,
            'content'         => $text,
            'status'          => MessageStatus::SENT,
        ]);

        broadcast(new MessageSent($message));
        return $message;
    }

    /**
     * Send voice message
     */
    public function sendAudioMessage(Conversation $conversation, User $sender, $audioFile): Message
    {
        $audioPath = $audioFile->store('voice_messages', 'public');

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $sender->id,
            'message_type'    => MessageType::AUDIO,
            'content'         => asset('storage/' . $audioPath),
            'status'          => MessageStatus::SENT,
        ]);

        broadcast(new MessageSent($message));
        return $message;
    }

    /**
     * Send file messages (photo, video, PDF or any other file)
     */
    public function sendFileMessage(Conversation $conversation, User $sender, $uploadedFile): Message
    {
        $extension    = strtolower($uploadedFile->getClientOriginalExtension());
        $storedPath   = $uploadedFile->store('file_messages', 'public');
        $originalName = $uploadedFile->getClientOriginalName();
        $size         = $uploadedFile->getSize();

        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
        $videoExts = ['mp4', 'mov', 'avi', 'mkv'];
        $pdfExts   = ['pdf'];

        if (in_array($extension, $imageExts)) {
            $messageType = MessageType::IMAGE;
            $fileType    = 'image';
        } elseif (in_array($extension, $videoExts)) {
            $messageType = MessageType::VIDEO;
            $fileType    = 'video';
        } elseif (in_array($extension, $pdfExts)) {
            $messageType = MessageType::PDF;
            $fileType    = 'pdf';
        } else {
            $messageType = MessageType::FILE;
            $fileType    = 'other';
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $sender->id,
            'message_type'    => $messageType,
            'content'         => null,
            'status'          => MessageStatus::SENT,
        ]);

        File::create([
            'message_id' => $message->id,
            'file_path'  => 'storage/' . $storedPath,
            'file_name'  => $originalName,
            'file_type'  => $fileType,
            'file_size'  => $size,
        ]);

        broadcast(new MessageSent($message));
        return $message;
    }
}
