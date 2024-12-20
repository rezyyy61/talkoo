// src/stores/voiceMessages.js

import { defineStore } from 'pinia';
import axiosInstance from '@/axios';
import { v4 as uuidv4 } from 'uuid';

export const useVoiceMessagesStore = defineStore('voiceMessages', {
  state: () => ({
    voiceMessages: [],
    loading: false,
    error: null,
    isRecording: false,
  }),

  getters: {
    getAllVoiceMessages: (state) => state.voiceMessages,
    getVoiceMessageById: (state) => (id) => state.voiceMessages.find(message => message.id === id),
    sentVoiceMessages: (state) => state.voiceMessages.filter(message => message.sent),
    unsentVoiceMessages: (state) => state.voiceMessages.filter(message => !message.sent),
    latestVoiceMessage: (state) => {
      if (state.voiceMessages.length === 0) return null;
      return state.voiceMessages[state.voiceMessages.length - 1];
    },
  },

  actions: {
    addVoiceMessage(audioBlob, audioURL) {
      const newMessage = {
        id: uuidv4(),
        audioBlob,
        audioURL,
        timestamp: new Date(),
        sent: false,
      };
      this.voiceMessages.push(newMessage);
    },

    removeVoiceMessage(id) {
      this.voiceMessages = this.voiceMessages.filter(message => message.id !== id);
    },

    setRecordingState(state) {
      this.isRecording = state;
    },

    async sendVoiceMessage(id) {
      const message = this.getVoiceMessageById(id);
      if (!message) {
        this.error = 'Voice message not found.';
        return;
      }

      this.loading = true;
      this.error = null;

      try {
        const formData = new FormData();
        formData.append('audio', message.audioBlob, `voice_${message.id}.webm`);
        formData.append('timestamp', message.timestamp.toISOString());
        formData.append('content', '');

        const response = await axiosInstance.post('/users/${receiverId}/messages', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        });

        if (response.status === 201) {
          message.sent = true;
        } else {
          this.error = 'Error sending voice message.';
        }
      } catch (err) {
        this.error = 'Error sending voice message.';
      } finally {
        this.loading = false;
      }
    },

    async sendAllUnsentVoiceMessages() {
      const unsent = this.unsentVoiceMessages;
      for (const message of unsent) {
        await this.sendVoiceMessage(message.id);
      }
    },

    async fetchVoiceMessages() {
      this.loading = true;
      this.error = null;

      try {
        const response = await axiosInstance.get('/voice-messages');
        if (response.status === 200) {
          this.voiceMessages = response.data.map(msg => ({
            id: msg.id,
            audioBlob: null,
            audioURL: msg.audioURL,
            timestamp: new Date(msg.timestamp),
            sent: true,
          }));
        } else {
          this.error = 'Error fetching voice messages.';
        }
      } catch (err) {
        this.error = 'Error fetching voice messages.';
      } finally {
        this.loading = false;
      }
    },
  },

  persist: true,
});
