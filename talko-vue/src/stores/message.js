// src/stores/message.js
import { defineStore } from 'pinia';
import axiosInstance from '@/axios';
import { useAuthStore } from '@/stores/auth';
import { useFriendshipStore } from "@/stores/friendship.js";

export const useMessageStore = defineStore('message', {
  state: () => ({
    messages: [],
    conversationId: null,
    receiverId: null,
    isLoading: false,
    hasError: false,
    error: null,
    isListening: false,
    typingUsers: [],
    lastMessages: {},
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
      this.listenForNewMessages();
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

    listenForNewMessages() {
      if (!this.conversationId) {
        console.error('Cannot listen for messages without a conversation ID.');
        return;
      }

      if (!window.Echo) {
        console.error('Laravel Echo is not initialized.');
        return;
      }

      if (this.isListening) {
        return;
      }

      const channelName = `conversation.${this.conversationId}`;

      // Check if channel is already subscribed
      if (window.Echo.connector.channels[`private-${channelName}`]) {
        console.log(`Leaving existing channel: private-${channelName}`);
        window.Echo.leave(channelName);
      }

      window.Echo.private(channelName)
        .listen('MessageSent', (newMessage) => {
          const exists = this.messages.some(msg => msg.id === newMessage.id);
          if (!exists) {
            this.messages.push(newMessage);
          }
        })
        .listen('MessageRead', (data) => {
          const messageIds = data.message_Ids;
          if (!messageIds || !Array.isArray(messageIds)) {
            console.error('message_ids is not defined or not an array');
            return;
          }
          this.messages = this.messages.map(msg => {
            if (messageIds.includes(msg.id)) {
              return { ...msg, status: 'read' };
            }
            return msg;
          });
        })
        .listen('UserTyping', (data) => {
          this.handleUserTyping(data)
        })

      this.isListening = true;
    },

    async userTyping(isTyping) {

      if (!this.conversationId) return;

      try {
        await axiosInstance.post(`/conversations/${this.conversationId}/typing`, {
          is_typing: isTyping,
        });
      } catch (error) {
        console.error('Error sending typing status:', error);
      }
    },

    handleUserTyping(data) {
      const authStore = useAuthStore();
      const currentUserId = authStore.user ? authStore.user.id : null;

      const { user_id, is_typing, user_name } = data;

      if (currentUserId && user_id === currentUserId) {
        return;
      }

      if (is_typing) {
        if (!this.typingUsers.some(user => user.id === user_id)) {
          this.typingUsers.push({ id: user_id, name: user_name, timeout: null });
        }

        const user = this.typingUsers.find(user => user.id === user_id);
        if (user) {
          if (user.timeout) {
            clearTimeout(user.timeout);
          }
          user.timeout = setTimeout(() => {
            this.removeTypingUser(user_id);
          }, 3000);
        }
      } else {

        this.removeTypingUser(user_id);
      }
    },

    removeTypingUser(userId) {
      this.typingUsers = this.typingUsers.filter(user => user.id !== userId);
    },

    async fetchLastMessage(conversationId) {
      try {
        const response = await axiosInstance.get(`/conversations/${conversationId}/messages?limit=1`);
        return response.data.data[0] || null;
      } catch (error) {
        console.error('Error fetching last message:', error);
        return null;
      }
    },

    async fetchAllLastMessages() {
      try {
        // Assuming friendshipStore.friends contains conversationId for each friend
        const friendshipStore = useFriendshipStore();
        for (const friend of friendshipStore.friends) {
          if (friend.conversationId) {
            const lastMessage = await this.fetchLastMessage(friend.conversationId);
            this.lastMessages[friend.id] = lastMessage;
            // Optionally, you can assign it directly to the friend object
            friend.lastMessage = lastMessage;
          }
        }
      } catch (error) {
        console.error('Error fetching all last messages:', error);
      }
    },
  }
});
