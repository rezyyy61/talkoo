<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Enums\MessageType;
use App\Enums\MessageStatus;

class MessageModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_creation()
    {

        $user = User::factory()->create();
        $conversation = Conversation::factory()->create();


        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message_type' => MessageType::TEXT,
            'content' => 'This is a test message.',
            'status' => MessageStatus::SENT,
        ]);


        $this->assertEquals($conversation->id, $message->conversation_id);
        $this->assertEquals($user->id, $message->sender_id);
        $this->assertEquals(MessageType::TEXT, $message->message_type);
        $this->assertEquals('This is a test message.', $message->content);
        $this->assertEquals(MessageStatus::SENT, $message->status);
    }
}
