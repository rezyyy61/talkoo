<?php

namespace App\Http\Controllers;

use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function sendMessage(Request $request, $receiverId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $sender = auth()->user();
        $receiver = User::findOrFail($receiverId);

        // Ensure exactly two users
        $conversation = Conversation::where('is_group', false)
            ->whereHas('users', function ($query) use ($sender, $receiver) {
                $query->where('user_id', $sender->id)
                    ->orWhere('user_id', $receiver->id);
            }, '=', 2)
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create(['is_group' => false]);
            $conversation->users()->attach([$sender->id, $receiver->id], ['joined_at' => now()]);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'message_type' => MessageType::TEXT,
            'content' => $request->input('content'),
            'status' => MessageStatus::SENT,
        ]);

        broadcast(new MessageSent($message));

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $message,
            'conversation_id' => $conversation->id,
        ], 201);
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
