<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Reaction;
use Illuminate\Support\Facades\Auth;
use App\Events\ReactionAdded;

class ReactionController extends Controller
{
    public function react(Request $request, $messageId)
    {
        $request->validate([
            'emoji' => 'required|string',
        ]);

        $user = Auth::user();
        $message = Message::findOrFail($messageId);

        // Prevent reacting to one's own message (optional)
        if ($message->sender_id === $user->id) {
            return response()->json(['error' => 'Cannot react to your own message.'], 403);
        }

        // Create or delete reaction (toggle)
        $reaction = Reaction::where('message_id', $messageId)
            ->where('user_id', $user->id)
            ->where('emoji', $request->emoji)
            ->first();

        if ($reaction) {
            $reaction->delete();

            // Broadcast reaction removed
            broadcast(new ReactionAdded($message, $user, $request->emoji, false))->toOthers();

            return response()->json(['message' => 'Reaction removed.'], 200);
        } else {
            $reaction = Reaction::create([
                'message_id' => $messageId,
                'user_id' => $user->id,
                'emoji' => $request->emoji,
            ]);

            // Broadcast reaction added
            broadcast(new ReactionAdded($message, $user, $request->emoji, true))->toOthers();

            return response()->json(['message' => 'Reaction added.'], 201);
        }
    }
}
