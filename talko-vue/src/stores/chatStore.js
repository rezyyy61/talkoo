// src/stores/chatStore.js

import { defineStore } from "pinia";
import axiosInstance from '@/axios';

export const useChatStore = defineStore('chat', {
  state: () => ({
    messages: [],
    conversationId: null,
    receiverId: null,
    isLoading: false,
    error: null,
    isListening: false,
    typingUsers: []
  }),

  actions: {
    async setReceiverId(receiverId) {
      this.receiverId = receiverId;

      // Fetch or create the conversation
      await this.fetchConversationId(receiverId);

      // Fetch messages
      if (this.conversationId) {
        await this.fetchMessages(this.conversationId);
        this.listenForNewMessages();
      }
    },

    async fetchConversationId(receiverId) {
      try {
        const response = await axiosInstance.get(`/conversations/by-user/${receiverId}`);
        this.conversationId = response.data.conversation_id;
      } catch (error) {
        console.error('Error fetching conversation ID:', error);
        this.error = 'Failed to fetch conversation.';
      }
    },

    async fetchMessages(conversationId) {
      this.isLoading = true;
      this.error = null;
      try {
        const response = await axiosInstance.get(`/conversations/${conversationId}/messages`);
        this.messages = response.data.data;
      } catch (error) {
        console.error('Error fetching messages:', error);
        this.error = 'Failed to load messages.';
      } finally {
        this.isLoading = false;
      }
    },

    async sendMessage(content) {
      if (!this.conversationId) {
        console.error('No conversation ID set.');
        this.error = 'No conversation selected.';
        return;
      }

      try {
        await axiosInstance.post(`/users/${this.receiverId}/messages`, { content });
      } catch (error) {
        console.error('Error sending message:', error);
        this.error = 'Failed to send message.';
      }
    },

    async markMessagesAsRead(conversationId) {
      try {
        await axiosInstance.patch(`/conversations/${conversationId}/messages/read`);
      } catch (error) {
        console.error('Error marking messages as read:', error);
      }
    },

    handleUserTyping(data) {
      const { user_id, is_typing } = data;
      if (is_typing) {
        if (!this.typingUsers.includes(user_id)) {
          this.typingUsers.push(user_id);
        }
      } else {
        this.typingUsers = this.typingUsers.filter(id => id !== user_id);
      }
    },



  },

  getters: {
    getMessages: (state) => state.messages,
    getReceiverId: (state) => state.receiverId,
    hasError: (state) => !!state.error,
  },
});
