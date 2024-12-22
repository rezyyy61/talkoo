<!-- src/dashboard/RightColumn/ChatInput.vue -->
<template>
  <div class="chatInput p-4 bg-white mx-6 rounded-2xl flex items-center space-x-2 relative">
    <div class="relative group">
      <button
        class="text-gray-400 hover:text-gray-600 focus:outline-none transition-colors duration-200 p-2"
        @click="triggerFileInput"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-6 w-6"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path d="M21 10.5V21a2 2 0 01-2 2h-12a2 2 0 01-2-2V6.5a2 2 0 012-2h9l5 5z" />
          <path d="M14 7l5 5" />
        </svg>
      </button>
      <transition name="fade">
        <div
          v-if="showFilePreview && selectedFile"
          class="absolute bottom-full left-0 mb-2  bg-white rounded-lg shadow-lg p-2 z-10"
        >
          <!-- کامپوننت نمایش فایل -->
          <FileDisplay :file="selectedFile" />

          <!-- دکمه بستن اگر بخواهید -->
          <button
            class="mt-2 text-sm text-red-600 hover:underline"
            @click="removeSelectedFile"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18" />
              <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </button>
        </div>
      </transition>
    </div>

    <input
      type="file"
      ref="fileInput"
      class="hidden"
      @change="handleFileUpload"
    />

    <VoiceRecorder />

    <Emoji @emoji-selected="insertEmoji" />

    <div class="relative flex-1">
      <Recording v-if="isRecording || latestAudioURL" />
      <input
        v-else
        type="text"
        v-model="messageContent"
        ref="messageInput"
        placeholder="Type your message..."
        class="w-full bg-teal-500 text-white rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-shadow duration-200"
        @keyup.enter="submitMessage"
        @input="handleTyping"
        @blur="stopTyping"
      />
    </div>

    <button
      @click="submitMessage"
      :disabled="!canSend"
      class="ml-2 bg-teal-500 hover:bg-blue-600 text-white rounded-full p-2 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center transition-colors duration-200 relative"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-6 w-6 transform rotate-45"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <path d="M22 2L11 13" />
        <path d="M22 2L15 22L11 13L2 9L22 2Z" />
      </svg>

      <svg
        v-if="isSending"
        class="animate-spin h-6 w-6 text-white absolute"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
      >
        <circle
          class="opacity-25"
          cx="12"
          cy="12"
          r="10"
          stroke="currentColor"
          stroke-width="4"
        ></circle>
        <path
          class="opacity-75"
          fill="currentColor"
          d="M4 12a8 8 0 018-8v8H4z"
        ></path>
      </svg>
    </button>
  </div>
</template>

<script>
import { ref, computed, nextTick } from 'vue';
import { useChatStore } from "@/stores/chatStore.js";
import { useVoiceMessagesStore } from '@/stores/voiceMessages';
import axiosInstance from "@/axios.js";
import VoiceRecorder from "@/Layouts/voices/VoiceRecorder.vue";
import Recording from "@/Layouts/voices/Recording.vue";
import Emoji from "@/Layouts/emoji/Emoji.vue";
import FileDisplay from "@/Layouts/file/FileDisplay.vue";

export default {
  name: 'ChatInput',
  components: { VoiceRecorder, Recording, Emoji, FileDisplay },

  setup() {
    const chatStore = useChatStore();
    const voiceStore = useVoiceMessagesStore();

    const messageInput = ref(null);
    const messageContent = ref('');
    const fileInput = ref(null);
    const selectedFile = ref(null);
    const showFilePreview = ref(false);

    const isSending = ref(false);
    const isRecording = computed(() => voiceStore.isRecording);
    const latestAudioURL = computed(() => voiceStore.latestVoiceMessage?.audioURL || null);
    const recordedBlob = computed(() => voiceStore.latestVoiceMessage?.audioBlob || null);

    const receiverId = computed(() => chatStore.receiverId);

    const triggerFileInput = () => {
      fileInput.value.click();
    };

    const handleFileUpload = (event) => {
      const files = event.target.files;
      if (files && files.length > 0) {
        selectedFile.value = files[0];
        showFilePreview.value = true;
        event.target.value = '';
      }
    };

    const removeAudio = () => {
      if (voiceStore.latestVoiceMessage) {
        voiceStore.removeVoiceMessage(voiceStore.latestVoiceMessage.id);
      }
    };

    const removeSelectedFile = () => {
      selectedFile.value = null;
      showFilePreview.value = false;
    };

    const submitMessage = async () => {
      if (
        !receiverId.value ||
        (messageContent.value.trim() === '' && !selectedFile.value && !recordedBlob.value)
      ) {
        return;
      }

      isSending.value = true;
      try {
        const formData = new FormData();

        if (selectedFile.value) {
          formData.append('file', selectedFile.value);
        }
        if (recordedBlob.value) {
          formData.append('audio', recordedBlob.value, `voice_${voiceStore.latestVoiceMessage.id}.webm`);
        }
        if (messageContent.value.trim()) {
          formData.append('content', messageContent.value.trim());
        }

        const response = await axiosInstance.post(`/users/${receiverId.value}/messages`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });

        if (response.status === 201) {
          messageContent.value = '';
          selectedFile.value = null;
          removeAudio();
          removeSelectedFile();
        }
      } catch (error) {
        console.error('Error sending message:', error);
      } finally {
        isSending.value = false;
      }
    };

    let typingTimeout = null;
    const TYPING_DELAY = 3000;

    const handleTyping = () => {
      startTyping();
      clearTimeout(typingTimeout);
      typingTimeout = setTimeout(stopTyping, TYPING_DELAY);
    };

    const startTyping = async () => {
      if (chatStore.conversationId) {
        try {
          await axiosInstance.post(`/conversations/${chatStore.conversationId}/typing`, { is_typing: true });
        } catch (error) {
          console.error('Error sending typing status:', error);
        }
      }
    };

    const stopTyping = async () => {
      if (chatStore.conversationId) {
        try {
          await axiosInstance.post(`/conversations/${chatStore.conversationId}/typing`, { is_typing: false });
        } catch (error) {
          console.error('Error sending typing status:', error);
        }
      }
    };

    const insertEmoji = (emoji) => {
      if (messageInput.value) {
        const textarea = messageInput.value;
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const before = messageContent.value.substring(0, start);
        const after = messageContent.value.substring(end);
        messageContent.value = before + emoji + after;
        nextTick(() => {
          textarea.selectionStart = textarea.selectionEnd = start + emoji.length;
          textarea.focus();
        });
      }
    };

    const canSend = computed(() => {
      return (
        !isRecording.value &&
        (messageContent.value.trim() !== '' || recordedBlob.value || selectedFile.value) &&
        receiverId.value &&
        !isSending.value
      );
    });

    return {
      messageContent,
      isSending,
      isRecording,
      latestAudioURL,
      recordedBlob,
      fileInput,
      selectedFile,
      messageInput,
      receiverId,
      submitMessage,
      handleFileUpload,
      triggerFileInput,
      removeAudio,
      handleTyping,
      insertEmoji,
      canSend,
      showFilePreview,
      removeSelectedFile,
    };
  },
};
</script>


<style scoped>
button svg {
  transition: transform 0.2s, box-shadow 0.2s;
}

button:hover svg {
  transform: scale(1.1);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Spinner Styling */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  100% {
    transform: rotate(360deg);
  }
}

/* Ensure the waveform container has proper height */
.waveform-container {
  position: relative;
  width: 100%;
  height: 80px; /* Adjust as needed */
}

/* Play Button Styling */
.play-button {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(255, 255, 255, 0.8);
  border: none;
  border-radius: 50%;
  padding: 6px;
  cursor: pointer;
  transition: background 0.2s;
}

.play-button:hover {
  background: rgba(255, 255, 255, 1);
}
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
