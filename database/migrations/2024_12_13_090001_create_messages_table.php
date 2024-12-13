<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id');
            $table->foreignId('sender_id');
            $table->enum('message_type', ['text', 'image', 'video', 'audio', 'pdf', 'file', 'emoji', 'mixed'])->default('text');
            $table->text('content')->nullable();
            $table->enum('status', ['sent', 'received', 'read'])->default('sent');
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');

            $table->index('conversation_id');
            $table->index('sender_id');
            $table->index('created_at');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
