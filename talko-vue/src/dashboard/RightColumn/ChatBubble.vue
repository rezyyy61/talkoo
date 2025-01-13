<!-- src/dashboard/RightColumn/ChatBubble.vue -->
<template>
  <div :class="bubbleContainerClasses">
    <!-- (Optional) Avatar section is commented out in your code -->

    <!-- Message Bubble -->
    <div
      class="relative inline-block p-4 rounded-2xl shadow-md"
      @contextmenu.prevent="onRightClick"
      :class="['relative max-w-lg p-4 rounded-2xl shadow-md flex flex-col', bubbleBackground, messageAlignment]"
    >
      <!-- Replied Message Preview -->
      <div v-if="repliedMessageDisplay" class="replied-message-preview p-2 bg-gray-200 dark:bg-gray-800 rounded-lg mb-2">
        <div class="flex items-center mb-1">
          <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">Replying to:</span>
        </div>
        <div class="text-sm text-gray-800 dark:text-gray-200 break-words" v-html="repliedMessageDisplay"></div>
      </div>

      <!-- Message Content -->
      <div v-if="message.message_type === 'text'" class="text-sm text-gray-900 dark:text-gray-100 break-words">
        {{ message.content }}
      </div>
      <div v-else-if="message.message_type === 'audio'" class="w-full">
        <AudioWaveform :audioUrl="message.content" />
      </div>
      <div
        v-else-if="['file', 'image', 'video', 'pdf'].includes(message.message_type)"
        class="file-content mt-2"
      >
        <ChatFileDisplay
          v-for="file in message.files"
          :key="file.id"
          :file="file"
        />
      </div>

      <!-- Timestamp and Status -->
      <div class="flex justify-end items-center mt-3 text-xs text-gray-500 dark:text-gray-400">
        <span>{{ formattedTime }}</span>
        <span v-if="isSender" class="ml-2 flex items-center">
          <svg
            v-if="message.status === 'read'"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
            class="w-4 h-4 text-green-500"
            aria-label="Message Read"
          >
            <path
              d="M20.292 6.292a1 1 0 011.416 1.416l-9.003 9.003a1 1 0 01-1.415 0L7.293 13.415a1 1 0 011.415-1.415L11.29 14.58l8.998-8.289z"
            />
            <path
              d="M13.292 8.292a1 1 0 011.416 1.416L11.707 13.71a1 1 0 01-1.415 0L9.293 12.41a1 1 0 011.415-1.415L13.29 8.292z"
            />
          </svg>
          <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
            class="w-4 h-4 text-gray-400"
            aria-label="Message Sent"
          >
            <path
              d="M20.292 6.292a1 1 0 011.416 1.416l-9.003 9.003a1 1 0 01-1.415 0L7.293 13.415a1 1 0 011.415-1.415L11.29 14.58l8.998-8.289z"
            />
          </svg>
        </span>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, watch } from 'vue';
import AudioWaveform from "@/Layouts/voices/AudioWaveform.vue";
import ChatFileDisplay from "@/Layouts/file/ChatFileDisplay.vue";
import { useMessageStore } from '@/stores/message.js';

export default defineComponent({
  name: 'ChatBubble',
  components: { AudioWaveform, ChatFileDisplay },
  props: {
    message: {
      type: Object,
      required: true,
    },
    currentUserId: {
      type: Number,
      required: true,
    },
  },
  emits: ['right-clicked'],

  setup(props, { emit }) {
    const messageStore = useMessageStore();

    // Check if this message is sent by current user
    const isSender = computed(() => {
      return props.message.sender.id === props.currentUserId;
    });

    // Bubble styling
    const bubbleContainerClasses = computed(() => {
      return isSender.value ? 'flex justify-end' : 'flex justify-start';
    });

    const bubbleBackground = computed(() => {
      return isSender.value
        ? 'bg-blue-500 dark:bg-blue-600 text-white'
        : 'bg-gray-300 dark:bg-gray-700 text-gray-900 dark:text-gray-100';
    });

    const messageAlignment = computed(() => {
      return isSender.value ? 'items-end' : 'items-start';
    });

    // Time formatting
    const formattedTime = computed(() => {
      const date = new Date(props.message.created_at);
      return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    });

    // Helper to get name initials
    const getInitials = (name: string): string => {
      if (!name) return '?';
      const names = name.trim().split(' ');
      if (names.length === 1) {
        return names[0].charAt(0).toUpperCase();
      }
      return (names[0].charAt(0) + names[1].charAt(0)).toUpperCase();
    };

    const capitalize = (str: string): string => str.charAt(0).toUpperCase() + str.slice(1);

    // Find the message that is being replied to
    const repliedMessage = computed(() => {
      if (!props.message.reply_to_message_id) return null;
      return messageStore.getMessageById(props.message.reply_to_message_id);
    });

    // Optional: watch changes to repliedMessage to debug
    watch(
      () => repliedMessage.value,
      (newVal) => {
        console.log('ChatBubble: repliedMessage changed to:', newVal);
      }
    );

    const getThumbnailUrl = (message) => {
      if (!message.files || message.files.length === 0) return '';
      const file = message.files[0];
      if (file.file_path) {
        return file.file_path;
      }
      return file.url;
    };

    // Display snippet of replied message
    const repliedMessageDisplay = computed(() => {
      if (!repliedMessage.value) return '';
      const parentMsg = repliedMessage.value;

      if (parentMsg.message_type === 'text') {
        return parentMsg.content.length > 50
          ? parentMsg.content.substring(0, 50) + '...'
          : parentMsg.content;
      } else if (parentMsg.message_type === 'image') {
        return `<img src="${getThumbnailUrl(parentMsg)}" alt="Replied Image" class="w-16 h-16 object-cover rounded" />`;
      } else if (parentMsg.message_type === 'video') {
        return `<video src="${getThumbnailUrl(parentMsg)}" class="w-16 h-16 object-cover rounded" muted preload="metadata"></video>`;
      } else if (parentMsg.message_type === 'file' || parentMsg.message_type === 'pdf') {
        return `<div class="flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" />
                    <path d="M14 2v6h6" />
                  </svg>
                  <span class="text-sm text-gray-700 dark:text-gray-200">${parentMsg.files[0].name}</span>
                </div>`;
      }
      return `[${capitalize(parentMsg.message_type)} message]`;
    });

    // Right-click menu handler
    const onRightClick = (event: MouseEvent) => {
      event.preventDefault();
      emit('right-clicked', { message: props.message, event });
    };

    return {
      isSender,
      bubbleContainerClasses,
      bubbleBackground,
      messageAlignment,
      formattedTime,
      getInitials,
      onRightClick,
      repliedMessageDisplay,
      capitalize,
    };
  },
});
</script>

<style scoped>
.replied-message-preview {
  border-left: 4px solid #2563eb;
  padding-left: 8px;
  margin-bottom: 8px;
  background-color: #f3f4f6;
  border-radius: 0.5rem;
}

.replied-message-preview .font-semibold {
  color: #3b82f6;
}

.avatar img {
  transition: transform 0.2s, box-shadow 0.2s;
}

.avatar img:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.bubbleContainerClasses {
  /* Ensures proper alignment */
  display: flex;
  align-items: flex-start;
}

@media (max-width: 768px) {
  .replied-message-preview {
    max-width: 100%;
  }
}

.replied-message-preview img,
.replied-message-preview video {
  width: 4rem;
  height: 4rem;
  object-fit: cover;
  border-radius: 0.5rem;
}

.replied-message-preview svg {
  width: 1.25rem;
  height: 1.25rem;
}

.replied-message-preview div {
  display: flex;
  align-items: center;
}
</style>
