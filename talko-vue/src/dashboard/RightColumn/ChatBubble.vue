<!-- ChatBubble.vue -->
<template>
  <!-- Bubble Wrapper -->
  <div
    class="bg-red-400/15"
    :class="[bubbleClasses, { 'blur-sm opacity-50': isBlurred }]"
    @mouseover="isHovered = true"
    @mouseleave="isHovered = false"
    @contextmenu.prevent="handleRightClick"
  >
  <!-- Avatar or sender initials (only shows for non-sender) -->
  <template v-if="!isSender">
    <img
      v-if="message.sender.profile.avatarImage"
      :src="`/storage/${message.sender.profile.avatarImage}`"
      alt="Receiver Avatar"
      class="w-10 h-10 rounded-full shadow-md border-2 border-white mx-3 flex-shrink-0"
    />
    <div
      v-else
      :style="{ backgroundColor: message.sender.profile.avatarColor || '#ffffff' }"
      class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white mx-3 shadow-md flex-shrink-0"
    >
      {{ getInitials(message.sender.name) }}
    </div>
  </template>

  <!-- Message Bubble -->
  <div class="relative">
    <div
      :class="[bubbleBackground, 'inline-block max-w-xl p-3 rounded-2xl shadow-md flex flex-col transition-all duration-300', { 'mt-4': isHovered }]"
    >
      <!-- Text message -->
      <div v-if="message.message_type === 'text'" class="text-sm text-white">
        {{ message.content }}
      </div>

      <!-- Audio message -->
      <div v-else-if="message.message_type === 'audio'" class="w-full">
        <AudioWaveform :audioUrl="message.content" />
      </div>

      <!-- File / Image / Video / PDF message -->
      <div
        v-else-if="['file', 'image', 'video', 'pdf'].includes(message.message_type)"
        class="file-content"
      >
        <ChatFileDisplay
          v-for="file in message.files"
          :key="file.id"
          :file="file"
        />
      </div>

      <!-- Timestamp + read status -->
      <div class="flex justify-between items-center mt-2 text-xs text-gray-200">
        <span>{{ formattedTime }}</span>
        <span v-if="isSender" class="ml-2">
            <!-- "Read" double-check icon -->
            <svg
              v-if="message.status === 'read'"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              fill="currentColor"
              class="w-4 h-4 text-green-500"
            >
              <path
                d="M20.292 6.292a1 1 0 011.416 1.416l-9.003 9.003a1 1 0 01-1.415 0L7.293 13.415a1 1 0 011.415-1.415L11.29 14.58l8.998-8-289z"
              />
              <path
                d="M13.292 8.292a1 1 0 011.416 1.416L11.707 13.71a1 1 0 01-1.415 0L9.293 12.41a1 1 0 011.415-1.415L13.29 8.292z"
              />
            </svg>
          <!-- "Sent" single-check icon -->
            <svg
              v-else
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              fill="currentColor"
              class="w-4 h-4 text-gray-400"
            >
              <path
                d="M20.292 6.292a1 1 0 011.416 1.416l-9.003 9.003a1 1 0 01-1.415 0L7.293 13.415a1 1 0 011.415-1.415L11.29 14.58l8.998-8-289z"
              />
            </svg>
          </span>
      </div>

      <!-- Existing Reactions -->
      <div
        v-if="message.reactions && message.reactions.length > 0"
        class="reactions-display mt-2 flex flex-wrap"
      >
        <div
          v-for="reaction in groupedReactions"
          :key="reaction.emoji"
          class="flex items-center mr-2 mb-2"
        >
          <span class="text-xl mr-1">{{ reaction.emoji }}</span>
          <span class="text-xs text-white">{{ reaction.count }}</span>
        </div>
      </div>

      <!-- Reaction selector (shown on hover if it's not the sender) -->
      <div
        v-if="isHovered && !isSender"
        class="reactions-container mt-2 self-end"
      >
        <Reactions @react="handleReaction" />
      </div>
    </div>
  </div>
  </div>
</template>

<script lang="ts">
import { computed, ref } from 'vue';
import AudioWaveform from "@/Layouts/voices/AudioWaveform.vue";
import ChatFileDisplay from "@/Layouts/file/ChatFileDisplay.vue";
import Reactions from "@/dashboard/RightColumn/Reactions.vue";
import { useMessageStore } from '@/stores/message';

export default {
  name: 'ChatBubble',
  components: {
    AudioWaveform,
    ChatFileDisplay,
    Reactions,
  },
  props: {
    message: {
      type: Object,
      required: true,
    },
    currentUserId: {
      type: Number,
      required: true,
    },
    isBlurred: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['message-right-clicked'],
  setup(props, { emit }) {
    const messageStore = useMessageStore();
    const isHovered = ref(false);

    // Is this bubble from the current user?
    const isSender = computed(() => {
      return props.message.sender.id === props.currentUserId;
    });

    // Align bubble to the right if it's from the current user
    const bubbleClasses = computed(() => {
      return isSender.value
        ? 'inline-flex flex-row-reverse ml-auto items-start mb-4 transition-all duration-300 w-fit'
        : 'inline-flex mr-auto items-start mb-4 transition-all duration-300 w-fit';
    });

    // Background color: current user vs. other
    const bubbleBackground = computed(() => {
      return isSender.value ? 'bg-blue-500' : 'bg-gray-500';
    });

    // Convert created_at to local time
    const formattedTime = computed(() => {
      const date = new Date(props.message.created_at);
      return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    });

    // Extract user initials (in case there's no avatar)
    const getInitials = (name: string) => {
      if (!name) return '?';
      const names = name.trim().split(' ');
      return names.length > 1
        ? names[0].charAt(0).toUpperCase() + names[1].charAt(0).toUpperCase()
        : names[0].charAt(0).toUpperCase();
    };

    // Group reactions by emoji
    const groupedReactions = computed(() => {
      if (!props.message.reactions) return [];
      const reactionMap: Record<string, number> = {};
      props.message.reactions.forEach((r: any) => {
        if (reactionMap[r.emoji]) {
          reactionMap[r.emoji]++;
        } else {
          reactionMap[r.emoji] = 1;
        }
      });
      return Object.keys(reactionMap).map((emoji) => ({
        emoji,
        count: reactionMap[emoji],
      }));
    });

    // Add a reaction
    const handleReaction = (emoji: string) => {
      messageStore.reactToMessage({ messageId: props.message.id, emoji });
    };

    // Right click -> open context menu
    const handleRightClick = (event: MouseEvent) => {
      event.preventDefault();

      // Use clientX and clientY for viewport-relative positioning
      emit('message-right-clicked', {
        message: props.message,
        position: {
          x: event.clientX,
          y: event.clientY,
        },
      });
    };

    return {
      isHovered,
      isSender,
      bubbleClasses,
      bubbleBackground,
      formattedTime,
      getInitials,
      groupedReactions,
      handleReaction,
      handleRightClick,
    };
  },
};
</script>

<style scoped>
.flex-shrink-0 {
  flex-shrink: 0;
}

.reactions-container {
  display: flex;
  gap: 4px;
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.reactions-display {
  display: flex;
  flex-wrap: wrap;
}

.text-xl {
  font-size: 1.25rem;
}

.inline-block {
  transition: transform 0.3s ease, margin 0.3s ease;
}

.inline-block.mt-4 {
  margin-top: 1rem;
}

.relative {
  position: relative;
}

.absolute {
  z-index: 10;
}

.absolute.flex {
  flex-direction: row;
}

.space-x-2 > * + * {
  margin-left: 0.5rem;
}
</style>
