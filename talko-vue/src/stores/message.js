// src/stores/message.js
import { defineStore } from 'pinia';
import axiosInstance from '@/axios';

export const useMessageStore = defineStore('message', {
  state: () => ({
    messages: [],
    conversationId: null,
    receiverId: null,
    isLoading: false,
    hasError: false,
    error: null,
    typingUsers: [],
  }),

  actions: {
    setReceiverId(id) {
      this.receiverId = id;
    },
    setConversationId(id) {
      this.conversationId = id;
    },

    async getPrivateConversationId(receiverId) {
      try {
        const response = await axiosInstance.get(`/conversations/by-user/${receiverId}`);

        this.conversationId = response.data.conversation_id;
        return this.conversationId;
      } catch (error) {
        console.error(error);
        throw error;
      }
    },
    async getSameIPConversation() {
      try {
        this.isLoading = true;
        const response = await axiosInstance.get('/conversations/group/same-ip');
        this.conversationId = response.data.conversation_id;
        return this.conversationId;
      } catch (error) {
        console.error(error);
        throw error;
      } finally {
        this.isLoading = false;
      }
    },

    async fetchMessages() {
      if (!this.conversationId) {
        console.warn('No conversationId set');
        return;
      }
      try {
        const response = await axiosInstance.get(`/conversations/${this.conversationId}/messages`);
        this.messages = response.data.data;
      } catch (error) {
        console.error(error);
      }
    },

    async sendMessage(payload) {
      try {
        const formData = new FormData();

        formData.append('type', payload.type || 'private');

        if (payload.file) {
          formData.append('file', payload.file);
        }
        if (payload.content) {
          formData.append('content', payload.content);
        }
        if (payload.audio) {
          formData.append('audio', payload.audio);
        }
        const conversationId = payload.conversation_id || this.conversationId;
        if (conversationId) {
          formData.append('conversation_id', conversationId);
        }
        const receiverId = payload.receiver_id || this.receiverId;
        if (receiverId) {
          formData.append('receiver_id', receiverId);
        }

        const response = await axiosInstance.post('/messages/send', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
          maxBodyLength: Infinity,
          maxContentLength: Infinity,
        });
        console.log(response.data)
        this.messages.push(response.data.data);
      } catch (error) {
        console.error(error);
      }
    },

    async markAsRead() {
      if (!this.conversationId) {
        console.warn('No conversationId set for markAsRead');
        return;
      }
      try {
        await axiosInstance.patch(`/conversations/${this.conversationId}/messages/read`);
      } catch (error) {
        console.error(error);
      }
    },

    async userTyping(isTyping) {
      if (!this.conversationId) {
        console.warn('No conversationId set for userTyping');
        return;
      }
      try {
        await axiosInstance.post(`/conversations/${this.conversationId}/typing`, { is_typing: isTyping });
        if (isTyping) {

        } else {

        }
      } catch (error) {
        console.error(error);
      }
    },
  }
});
