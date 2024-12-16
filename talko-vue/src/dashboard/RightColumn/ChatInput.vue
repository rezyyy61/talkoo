<!-- src/dashboard/RightColumn/ChatInput.vue -->
<template>
  <div class="chatInput p-4 bg-[#ffffff] mx-6 rounded-2xl flex items-center space-x-2 relative">
    <!-- Enhanced Attachment Button with Tooltip -->
    <div class="relative group">
      <button
        @click="triggerFileInput"
        class="text-gray-400 hover:text-gray-200 focus:outline-none transition-colors duration-200 p-2"
        aria-label="Attach File"
      >
        <!-- New Custom Paperclip SVG -->
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
    </div>

    <!-- Hidden File Input -->
    <input
      type="file"
      ref="fileInput"
      class="hidden"
      @change="handleFileChange"
    />

    <!-- Voice Button with Tooltip -->
    <div class="relative group">
      <button
        @click="toggleVoiceRecording"
        class="text-gray-400 hover:text-gray-200 focus:outline-none transition-colors duration-200 p-2"
        aria-label="Send Voice Message"
      >
        <!-- Custom Microphone SVG -->
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
          <path d="M12 1a3 3 0 00-3 3v7a3 3 0 006 0V4a3 3 0 00-3-3z" />
          <path d="M5 10v2a7 7 0 0014 0v-2" />
          <path d="M12 19v4" />
          <path d="M8 23h8" />
        </svg>
      </button>
    </div>

    <!-- Emoji Picker Button with Tooltip -->
    <div class="relative group">
      <button
        @click="toggleEmojiPicker"
        class="text-gray-400 hover:text-gray-200 focus:outline-none transition-colors duration-200 p-2"
        aria-label="Insert Emoji"
      >
        <!-- Custom Smiley Face SVG -->
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
          <circle cx="12" cy="12" r="10" />
          <path d="M15 15a3 3 0 11-6 0" />
          <path d="M9 9h.01" />
          <path d="M15 9h.01" />
        </svg>
      </button>
    </div>

    <!-- Input Field -->
    <input
      type="text"
      v-model="messageContent"
      placeholder="Type your message..."
      class="flex-1 bg-[#02b0a6] text-white rounded-full px-4 py-4 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-shadow duration-200"
      @keyup.enter="submitMessage"
      @input="handleTyping"
      @blur="stopTyping"
    />

    <!-- Send Button -->
    <button
      @click="submitMessage"
      :disabled="!messageContent.trim() || !receiverId"
      class="ml-2 bg-[#02b0a6] hover:bg-blue-600 text-white rounded-full p-2 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center transition-colors duration-200"
      aria-label="Send Message"
    >
      <!-- Custom Paper Airplane SVG -->
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

      <!-- Spinner SVG -->
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

    <!-- Emoji Picker (Optional) -->
    <div v-if="showEmojiPicker" class="absolute bottom-16 left-0">
      <!-- Implement your emoji picker here -->
      <div class="bg-white p-2 rounded shadow-lg">
        <!-- Example Emoji Buttons -->
        <button @click="insertEmoji('😊')">😊</button>
        <button @click="insertEmoji('😂')">😂</button>
        <button @click="insertEmoji('❤️')">❤️</button>
        <!-- Add more emojis as needed -->
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import { useChatStore } from "@/stores/chatStore.js";
import axiosInstance from "@/axios.js";

export default {
  name: 'ChatInput',

  setup() {
    const messageContent = ref('');
    const chatStore = useChatStore();
    const fileInput = ref(null);
    const isSending = ref(false);
    const showEmojiPicker = ref(false);
    let typingTimeout = null
    const TYPING_DELAY = 3000

    const receiverId = computed(() => chatStore.receiverId);

    const submitMessage = async () => {
      if (messageContent.value.trim() !== '' && receiverId.value) {
        try {
          isSending.value = true;
          await chatStore.sendMessage(messageContent.value);
          messageContent.value = '';
        } catch (error) {
          console.error('Error sending message:', error);
          // Optionally, show an error message to the user
        } finally {
          isSending.value = false;
        }
      }
    };

    const handleTyping = () => {
      startTyping()
      clearTimeout(typingTimeout)
      typingTimeout = setTimeout(stopTyping, TYPING_DELAY)
    }

    const startTyping = async () => {
      if (chatStore.conversationId) {
        await axiosInstance.post(`/conversations/${chatStore.conversationId}/typing`, { is_typing: true });
      }
    };

    const stopTyping = async () => {
      if (chatStore.conversationId) {
        await axiosInstance.post(`/conversations/${chatStore.conversationId}/typing`, { is_typing: false });
      }
    };

    const triggerFileInput = () => {
      if (fileInput.value) {
        fileInput.value.click();
      }
    };

    const handleFileChange = (event) => {
      const files = event.target.files;
      if (files && files.length > 0) {
        // Handle file upload
        console.log('Selected files:', files);
        // You can implement file upload logic here
      }
    };

    const toggleVoiceRecording = () => {
      // Implement voice recording functionality
      console.log('Voice recording toggled');
    };

    const toggleEmojiPicker = () => {
      showEmojiPicker.value = !showEmojiPicker.value;
    };

    const insertEmoji = (emoji) => {
      messageContent.value += emoji;
      showEmojiPicker.value = false;
    };

    return {
      messageContent,
      submitMessage,
      fileInput,
      isSending,
      triggerFileInput,
      handleFileChange,
      toggleVoiceRecording,
      showEmojiPicker,
      toggleEmojiPicker,
      insertEmoji,
      receiverId,
      handleTyping,
      startTyping,
      stopTyping
    };
  }
};
</script>

<style scoped>
/* Optional: Add any component-specific styles here */

/* Smooth transitions for SVG transformations */
button svg {
  transition: transform 0.2s, box-shadow 0.2s;
}

button:hover svg {
  transform: scale(1.1);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Tooltip Styling */
.group:hover .group-hover\:opacity-100 {
  opacity: 1;
}

.group .opacity-0 {
  opacity: 0;
}

.group .group-hover\:opacity-100 {
  opacity: 1;
}

.tooltip {
  /* Additional tooltip styles if needed */
}

/* Recording Indicator */
button.text-red-500 svg {
  stroke: red;
  fill: red;
}

/* Spinner Styling for Sending Message */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  100% {
    transform: rotate(360deg);
  }
}
</style>
