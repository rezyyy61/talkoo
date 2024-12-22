<?php

namespace App\Http\Controllers;

use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Models\Conversation;
use App\Models\File;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;


class MessageController extends Controller
{
    public function sendMessage(Request $request, $receiverId)
    {
        $request->validate([
            'content' => 'nullable|string|max:1000',
            'file'    => 'nullable|file|max:20480',
            'audio'   => 'nullable|mimes:webm,mp3,wav|max:10240',
        ]);

        $sender = auth()->user();
        $receiver = User::findOrFail($receiverId);

        $conversation = Conversation::where('is_group', false)
            ->where('is_channel', false)
            ->whereHas('users', function ($q) use ($sender, $receiver) {
                $q->where('user_id', $sender->id)->orWhere('user_id', $receiver->id);
            }, '=', 2)
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create(['is_group' => false, 'is_channel' => false]);
            $conversation->users()->attach([$sender->id, $receiver->id], ['joined_at' => now()]);
        }

        if ($request->hasFile('file')) {
            $message = $this->sendFileMessage($request, $conversation);
            return response()->json(['success' => true, 'data' => $message], 201);
        }

        if ($request->hasFile('audio')) {
            $audioPath = $request->file('audio')->store('voice_messages', 'public');
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id'       => $sender->id,
                'message_type'    => MessageType::AUDIO,
                'content'         => asset('storage/' . $audioPath),
                'status'          => MessageStatus::SENT,
            ]);
            broadcast(new MessageSent($message));
            return response()->json(['success' => true, 'data' => $message], 201);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $sender->id,
            'message_type'    => MessageType::TEXT,
            'content'         => $request->input('content'),
            'status'          => MessageStatus::SENT,
        ]);

        broadcast(new MessageSent($message));
        return response()->json(['success' => true, 'data' => $message], 201);
    }

    private function sendFileMessage(Request $request, Conversation $conversation)
    {
        $uploadedFile = $request->file('file');
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
            'sender_id'       => auth()->id(),
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

    public function getConversation(Request $request, $receiverId)
    {
        $sender = auth()->user();
        $receiver = User::findOrFail($receiverId);

        // Fetch existing conversation between sender and receiver
        $conversation = Conversation::where('is_group', false)
            ->whereHas('users', function ($query) use ($sender, $receiver) {
                $query->where('user_id', $sender->id)
                    ->orWhere('user_id', $receiver->id);
            }, '=', 2)
            ->first();

        // If no conversation exists, create one
        if (!$conversation) {
            $conversation = Conversation::create(['is_group' => false]);
            $conversation->users()->attach([$sender->id, $receiver->id], ['joined_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
        ], 200);
    }
    public function getMessages($conversationId)
    {
        $user = auth()->user();

        $conversation = Conversation::where('id', $conversationId)
            ->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->firstOrFail();

        $messages = $conversation->messages()
            ->with(['sender', 'files'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages,
        ], 200);
    }

    public function markAsRead(Request $request, $conversationId)
    {
        $user = auth()->user();

        $unreadMessages = Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $user->id)
            ->where('status', MessageStatus::SENT)
            ->get();

        if ($unreadMessages->isEmpty()) {
            return response()->json(['success' => true, 'message' => 'No messages to update'], 200);
        }

        $messageIds = $unreadMessages->pluck('id')->toArray();
        Message::whereIn('id', $messageIds)->update(['status' => MessageStatus::READ]);

        broadcast(new MessageRead($messageIds, $user->id, $conversationId))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Messages marked as read',
            'data' => $messageIds,
        ], 200);
    }

    public function userTyping(Request $request, $conversationId)
    {
        $user = auth()->user();
        $isTyping = $request->input('is_typing', false);

        broadcast(new UserTyping($conversationId, $user->id, $isTyping))->toOthers();

        return response()->json(['success' => true]);
    }

}
