<?php

namespace App\Http\Controllers;

use App\Enums\MessageStatus;
use App\Events\MessageRead;
use App\Events\UserTyping;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\GroupService;
use App\Services\MessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    protected $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    public function sendMessage(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'type'       => 'required|in:private,group,channel',
            'receiver_id'=> 'nullable|exists:users,id',
            'conversation_id' => 'nullable|exists:conversations,id',
            'content'    => 'nullable|string|max:1000',
            'file'       => 'nullable|file|max:200480',
            'audio' => 'nullable|mimes:webm,mp3,wav,ogg|max:100240',
            'reply_to_message_id' => 'nullable|exists:messages,id',
        ]);

        $sender = auth()->user();
        $type   = $request->input('type');

        switch ($type) {
            case 'private':
                $receiverId   = $request->input('receiver_id');
                $conversation = $this->messageService->findOrCreatePrivateConversation($sender->id, $receiverId);
                break;

            case 'group':
                $conversationId = $request->input('conversation_id');
                $conversation   = $this->messageService->getGroupConversation($conversationId);
                break;

            case 'channel':
                $conversationId = $request->input('conversation_id');
                $conversation   = $this->messageService->getChannelConversation($conversationId);
                break;
        }

        $replyToMessageId = $request->input('reply_to_message_id');

        if ($request->hasFile('file')) {
            $message = $this->messageService->sendFileMessage($conversation, $sender, $request->file('file'), $replyToMessageId);
            return response()->json(['success' => true, 'data' => $message], 201);
        } elseif ($request->hasFile('audio')) {
            $message = $this->messageService->sendAudioMessage($conversation, $sender, $request->file('audio'), $replyToMessageId);
            return response()->json(['success' => true, 'data' => $message], 201);
        } else {
            $message = $this->messageService->sendTextMessage($conversation, $sender, $request->input('content'), $replyToMessageId);
            return response()->json(['success' => true, 'data' => $message], 201);
        }
    }

    public function getConversation(Request $request, $receiverId): \Illuminate\Http\JsonResponse
    {
        $sender = auth()->user();
        $receiver = User::findOrFail($receiverId);

        $conversation = $this->messageService->findOrCreatePrivateConversation($sender->id, $receiver->id);

        return response()->json([
            'success'          => true,
            'conversation_id'  => $conversation->id,
        ], 200);
    }

    public function getMessages($conversationId): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();

        $conversation = Conversation::where('id', $conversationId)
            ->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->firstOrFail();

        $messages = $conversation->messages()
            ->with(['sender.profile', 'files'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages,
        ], 200);
    }

    public function getMessageById($id): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();

        $message = Message::where('id', $id)
            ->whereHas('conversation', function ($query) use ($user) {
                $query->whereHas('users', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            })
            ->with(['sender.profile', 'files'])
            ->first();

        if (!$message) {
            return response()->json(['success' => false, 'message' => 'Message not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $message,
        ], 200);
    }

    public function markAsRead(Request $request, $conversationId): \Illuminate\Http\JsonResponse
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

    public function userTyping(Request $request, $conversationId): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        $isTyping = $request->input('is_typing', false);

        broadcast(new UserTyping($conversationId, $user->id, $isTyping, $user->name));

        return response()->json(['success' => true]);
    }

    public function getSameIPConversation(Request $request, GroupService $groupService): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $conversation = $groupService->createOrJoinGroup($user);

        return response()->json([
            'conversation_id' => $conversation->id,
        ], 200);
    }
}
