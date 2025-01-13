<!-- src/dashboard/RightColumn/ChatInput.vue -->
<template>
  <div class="chatInputContainer mx-6 p-4 bg-white rounded-2xl shadow flex flex-col space-y-2">

    <!-- Reply Preview -->
    <div v-if="replyToMessage" class="reply-preview-container p-2 bg-gray-100 dark:bg-gray-800 border-l-4 border-blue-500 rounded-lg flex items-start space-x-2">
      <div class="reply-icon text-blue-500">
        Replay To
      </div>
      <div class="reply-text flex-1">
        <span class="text-l text-gray-700 dark:text-gray-200">
          <span
            ref="replyTextRef"
            :class="{'truncate-text': !isExpanded}"
            :title="replyPreviewContent"
            v-html="replyPreviewDisplay"
          ></span>
        </span>
        <div class="buttons">
          <button
            v-if="isTruncated && !isExpanded"
            @click="toggleExpand"
            class="text-blue-500 text-sm ml-2"
          >
            more
          </button>
          <button
            v-if="isTruncated && isExpanded"
            @click="toggleExpand"
            class="text-blue-500 text-sm ml-2"
          >
            less
          </button>
        </div>
      </div>
      <button @click="cancelReply" class="text-gray-500 hover:text-gray-700">
        <!-- Close Icon -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <line x1="18" y1="6" x2="6" y2="18" />
          <line x1="6" y1="6" x2="18" y2="18" />
        </svg>
      </button>
    </div>

    <!-- Chat Input and Controls -->
    <div class="chatInput p-4 bg-white rounded-2xl flex items-center space-x-2 relative">

      <!-- File Upload Button -->
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
            class="absolute bottom-full left-0 mb-2 bg-white rounded-lg shadow-lg p-2 z-10"
          >
            <FileDisplay :file="selectedFile" />

            <button
              class="mt-2 text-sm text-red-600 hover:underline flex items-center"
              @click="removeSelectedFile"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
              </svg>
              Remove
            </button>
          </div>
        </transition>
      </div>

      <!-- Hidden File Input -->
      <input
        type="file"
        ref="fileInput"
        class="hidden"
        @change="handleFileUpload"
      />

      <!-- Voice Recorder -->
      <VoiceRecorder />

      <!-- Emoji Picker -->
      <Emoji @emoji-selected="insertEmoji" />

      <!-- Message Input -->
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

      <!-- Send Button -->
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
  </div>
</template>

<script>
import { ref, computed, nextTick, onMounted } from 'vue';
import { useMessageStore } from "@/stores/message.js";
import { useVoiceMessagesStore } from '@/stores/voiceMessages';
import VoiceRecorder from "@/Layouts/voices/VoiceRecorder.vue";
import Recording from "@/Layouts/voices/Recording.vue";
import Emoji from "@/Layouts/emoji/Emoji.vue";
import FileDisplay from "@/Layouts/file/FileDisplay.vue";

export default {
  name: 'ChatInput',
  emits: ['message-sent'],
  components: { VoiceRecorder, Recording, Emoji, FileDisplay },
  props: {
    chatType: {
      type: String,
      required: true,
    }
  },
  setup(props, { emit }) {
    const messageStore = useMessageStore();
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
    const receiverId = computed(() => messageStore.receiverId);
    const conversationId = computed(() => messageStore.conversationId);
    const replyToMessage = computed(() => messageStore.replyToMessage);

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

    const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1);

    const submitMessage = async () => {
      if (
        !receiverId.value &&
        !conversationId.value &&
        (messageContent.value.trim() === '' && !selectedFile.value && !recordedBlob.value)
      ) {
        return;
      }

      isSending.value = true;
      try {
        const payload = {
          type: props.chatType,
          content: messageContent.value.trim(),
          file: selectedFile.value || null,
          audio: recordedBlob.value || null,
        };

        await messageStore.sendMessage(payload);

        messageContent.value = '';
        removeAudio();
        removeSelectedFile();

        emit('message-sent');

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
      try {
        await messageStore.userTyping(true);
      } catch (error) {
        console.error('Error sending typing status:', error);
      }
    };

    const stopTyping = async () => {
      try {
        await messageStore.userTyping(false);
      } catch (error) {
        console.error('Error sending typing status:', error);
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
        (receiverId.value || conversationId.value) &&
        !isSending.value
      );
    });

    const replyPreviewContent = computed(() => {
      if (!replyToMessage.value) return '';
      if (replyToMessage.value.message_type === 'text') {
        return replyToMessage.value.content;
      }
      // Handle other message types as needed
      return `[${capitalize(replyToMessage.value.message_type)} message]`;
    });

    const cancelReply = () => {
      messageStore.clearReplyToMessage();
    };

    const getThumbnailUrl = (message) => {
      if (!message.files || message.files.length === 0) return '';
      const file = message.files[0];
      if (file.file_path) {
        return file.file_path;
      }
      return file.url;
    };

    // مدیریت پیش‌نمایش بر اساس نوع پیام
    const replyPreviewDisplay = computed(() => {
      if (!replyToMessage.value) return '';
      const message = replyToMessage.value;
      if (message.message_type === 'text') {
        return message.content.length > 50
          ? message.content.substring(0, 50) + '...'
          : message.content;
      } else if (message.message_type === 'image') {
        return `<img src="${getThumbnailUrl(message)}" alt="Replied Image" class="w-16 h-16 object-cover rounded" />`;
      } else if (message.message_type === 'video') {
        return `<video src="${getThumbnailUrl(message)}" class="w-16 h-16 object-cover rounded" muted preload="metadata"></video>`;
      }
      return `[${capitalize(message.message_type)} message]`;
    });

    // Reactive properties for truncation
    const isTruncated = ref(false);
    const isExpanded = ref(false);
    const replyTextRef = ref(null);

    const checkTruncation = () => {
      const el = replyTextRef.value;
      if (el) {
        isTruncated.value = el.scrollWidth > el.clientWidth;
      }
    };

    const toggleExpand = () => {
      isExpanded.value = !isExpanded.value;
      nextTick(() => {
        checkTruncation();
      });
    };

    onMounted(() => {
      nextTick(() => {
        checkTruncation();
      });
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
      conversationId,
      submitMessage,
      handleFileUpload,
      triggerFileInput,
      removeAudio,
      handleTyping,
      insertEmoji,
      canSend,
      showFilePreview,
      removeSelectedFile,
      replyToMessage,
      replyPreviewContent,
      cancelReply,
      getThumbnailUrl,
      replyPreviewDisplay,
      isTruncated,
      isExpanded,
      toggleExpand,
      replyTextRef,
    };
  },
};
</script>


<style scoped>
.chatInputContainer {
  /* Ensures the container stacks elements vertically */
  display: flex;
  flex-direction: column;
}

.reply-preview-container {
  /* Adds padding and background for the reply preview */
  padding: 0.75rem;
  background-color: #f3f4f6; /* Tailwind's gray-100 */
  border-left: 4px solid #3b82f6; /* Tailwind's blue-500 */
  border-radius: 0.5rem;
  display: flex;
  align-items: flex-start;
}

.reply-icon {
  /* Styles for the reply icon */
  margin-right: 0.5rem;
}

.reply-text {
  /* Ensures the reply text takes up available space */
  flex: 1;
}

.buttons button {
  /* Styles for the more/less buttons */
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
}

.buttons button:hover {
  text-decoration: underline;
}

.truncate-text {
  display: inline-block;
  max-width: 300px; /* Adjust as needed */
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  100% {
    transform: rotate(360deg);
  }
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Additional Enhancements */
.chatInput {
  /* Removes absolute positioning */
  width: 100%;
}

.reply-preview-container svg {
  /* Ensures the reply icon has consistent sizing */
  width: 1.25rem;
  height: 1.25rem;
}

@media (max-width: 768px) {
  .reply-preview-container {
    /* Adjusts max-width for smaller screens */
    max-width: 100%;
  }

  .truncate-text {
    max-width: 200px;
  }
}
</style>
