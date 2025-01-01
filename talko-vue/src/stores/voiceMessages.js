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

  },

  persist: true,
});
