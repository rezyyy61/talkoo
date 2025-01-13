<!-- File: src/dashboard/RightColumn/ChatView.vue -->
<template>
  <div class="chatContainer relative flex flex-col h-full w-full">
    <div
      class="chatView flex-1 p-4 w-full h-full flex flex-col space-y-4 overflow-y-auto"
      ref="messagesContainer"
      @scroll="handleScroll"
    >
      <!-- Loading State -->
      <div v-if="messageStore.isLoading" class="flex justify-center items-center">
        <!-- Spinner -->
        <svg
          class="animate-spin h-8 w-8 text-blue-500"
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
      </div>

      <!-- Error State -->
      <div v-else-if="messageStore.hasError" class="text-red-500 text-center">
        {{ messageStore.error }}
      </div>

      <!-- Messages List -->
      <div v-else-if="messageStore.messages.length > 0" class="flex flex-col space-y-2">
        <ChatBubble
          v-for="message in messageStore.messages"
          :key="message.id"
          :message="message"
          :currentUserId="currentUserId"
          @right-clicked="handleMessageRightClick"
        />
      </div>

      <!-- No Messages Placeholder -->
      <div v-else class="text-center text-white text-xl font-bold">
        <p>No messages yet. Start the conversation!</p>
      </div>

      <!-- Typing Indicator -->
      <div
        v-if="messageStore.typingUsers.includes(messageStore.receiverId)"
        class="typing-indicator flex items-center"
      >
        <img alt="Receiver Avatar" class="w-10 h-10 rounded-full mr-2" />
        <span class="dot" style="background-color: #2563eb;"></span>
        <span class="dot" style="background-color: #2563eb;"></span>
        <span class="dot" style="background-color: #2563eb;"></span>
      </div>
    </div>

    <!-- New Message Indicator -->
    <div
      v-if="showNewMessageIndicator"
      class="new-message-indicator"
      @click="scrollToBottom"
      role="button"
      aria-label="Scroll to bottom"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-6 w-6"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
      <span v-if="newMessageCount > 0" class="message-count">{{ newMessageCount }}</span>
    </div>

    <!-- Context Menu -->
    <ContextMenu
      :visible="contextMenuVisible"
      :position="contextMenuPosition"
      :menuItems="contextMenuItems"
      @close="contextMenuVisible = false"
      @select="handleContextMenuSelect"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent, computed, ref, watch, onMounted, nextTick } from 'vue';
import { useMessageStore } from '@/stores/message';
import { useAuthStore } from '@/stores/auth';
import ChatBubble from "@/dashboard/RightColumn/ChatBubble.vue";
import ContextMenu from "@/dashboard/RightColumn/ContextMenu.vue";

interface RightClickPayload {
  message?: any;
  event?: MouseEvent;
}

export default defineComponent({
  name: 'ChatView',
  components: { ChatBubble, ContextMenu },
  setup() {
    const messageStore = useMessageStore();
    const authStore = useAuthStore();
    const currentUserId = computed(() => authStore.user?.id || null);
    const messagesContainer = ref<HTMLElement | null>(null);
    const newMessageCount = ref(0);
    const isUserAtBottom = ref(true);
    const isProgrammaticallyScrolling = ref(false);
    const userHasScrolledUp = ref(false);
    const contextMenuVisible = ref(false);
    const contextMenuPosition = ref({ x: 0, y: 0 });
    const contextMenuItems = ref<any[]>([]);
    const selectedMessageForContextMenu = ref(null);


    const showNewMessageIndicator = computed(() => newMessageCount.value > 0 || userHasScrolledUp.value);

    watch(contextMenuVisible, (visible) => {
      if (!messagesContainer.value) return;
      messagesContainer.value.style.overflowY = visible ? 'hidden' : 'auto';
    });

    function scrollToBottom() {
      if (!messagesContainer.value) return;
      isProgrammaticallyScrolling.value = true;
      messagesContainer.value.scrollTo({
        top: messagesContainer.value.scrollHeight,
        behavior: 'smooth',
      });
      newMessageCount.value = 0;
      userHasScrolledUp.value = false;
      isUserAtBottom.value = true;

      setTimeout(() => {
        isProgrammaticallyScrolling.value = false;
      }, 500);
    }

    function handleScroll() {
      if (isProgrammaticallyScrolling.value) {
        return;
      }
      if (!messagesContainer.value) return;
      const { scrollTop, scrollHeight, clientHeight } = messagesContainer.value;
      const threshold = 20;

      const atBottom = scrollHeight - scrollTop - clientHeight < threshold;

      if (atBottom) {
        isUserAtBottom.value = true;
        newMessageCount.value = 0;
        userHasScrolledUp.value = false;
        messageStore.markAsRead();
      } else {
        if (isUserAtBottom.value) {
          isUserAtBottom.value = false;
        }
        userHasScrolledUp.value = true;
      }
    }

    function handleMessageRightClick(
      { message, event }: RightClickPayload = {}
    ) {
      if (contextMenuVisible.value) {
        contextMenuVisible.value = false;
        return;
      }

      if (!message || !event) {
        return;
      }

      selectedMessageForContextMenu.value = message;
      contextMenuItems.value = getMenuItemsForMessage(message);
      contextMenuPosition.value = { x: event.clientX, y: event.clientY };
      contextMenuVisible.value = true;
    }

    function getMenuItemsForMessage(message: any) {
      const isSender = message.sender.id === currentUserId.value;
      const messageType = message.message_type;

      let items = [
        { label: 'Reply', action: 'reply' },
        { label: 'Forward', action: 'forward' },
        { label: 'Pin', action: 'pin' },
        { label: 'Copy Selected', action: 'copy-selected' },
        { label: 'Select', action: 'select' },
        { label: 'Delete', action: 'delete' },
      ];

      if (isSender && messageType === 'text') {
        items.push({ label: 'Edit', action: 'edit' });
      } else if (!isSender) {
        items.push({ label: 'Report', action: 'report' });
      }

      if (messageType === 'text') {
        items.push({ label: 'Copy Text', action: 'copy-text' });
      }
      if (messageType === 'audio' || messageType === 'file') {
        items.push({ label: 'Save as', action: 'save-as' });
      }

      return items;
    }

    function handleContextMenuSelect(item: any) {
      if (item.action === 'reply' && selectedMessageForContextMenu.value) {
        messageStore.setReplyToMessage(selectedMessageForContextMenu.value);
      }

      contextMenuVisible.value = false;
    }

    watch(
      () => messageStore.messages.length,
      async (newLength, oldLength) => {
        await nextTick();
        const newMessages = newLength - oldLength;

        if (isUserAtBottom.value) {
          scrollToBottom();
        } else {
          newMessageCount.value += newMessages;
        }
      }
    );


    watch(
      () => messageStore.receiverId,
      async (newReceiverId) => {
        if (newReceiverId) {
          messageStore.setReceiverId(newReceiverId);
          await nextTick();
          scrollToBottom();
        }
      },
      { immediate: true }
    );

    watch(
      () => messageStore.conversationId,
      async (newConversationId) => {
        if (newConversationId) {
          messageStore.setConversationId(newConversationId);
          await nextTick();
          scrollToBottom();
        }
      },
      { immediate: true }
    );

    onMounted(async () => {
      if (messageStore.messages.length > 0) {
        await nextTick();
        scrollToBottom();
      }
    });

    return {
      messageStore,
      currentUserId,
      messagesContainer,
      newMessageCount,
      showNewMessageIndicator,
      contextMenuVisible,
      contextMenuPosition,
      contextMenuItems,
      scrollToBottom,
      handleScroll,
      handleMessageRightClick,
      handleContextMenuSelect,
    };
  },
});
</script>

<style scoped>
.chatContainer {
  position: relative;
  height: 100%;
  width: 100%;
}

.chatView {
  position: relative;
  overflow-y: auto;
  height: 100%;
  overflow-anchor: none;
}

.typing-indicator {
  display: flex;
  gap: 0.25rem;
  justify-content: start;
  align-items: center;
}
.dot {
  width: 8px;
  height: 8px;
  background-color: #9ca3af;
  border-radius: 50%;
  animation: blink 1.4s infinite;
}
.dot:nth-child(1) {
  animation-delay: 0s;
}
.dot:nth-child(2) {
  animation-delay: 0.2s;
}
.dot:nth-child(3) {
  animation-delay: 0.4s;
}
@keyframes blink {
  0%, 80%, 100% {
    opacity: 0;
  }
  40% {
    opacity: 1;
  }
}

.new-message-indicator {
  position: absolute;
  bottom: 20px;
  right: 20px;
  background-color: #0f3078;
  color: white;
  padding: 0.75rem;
  border-radius: 9999px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  z-index: 10;
  transition: background-color 0.3s, opacity 0.3s;
}

.message-count {
  position: absolute;
  top: 30%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-weight: bold;
  font-size: 0.75rem;
  pointer-events: none;
}

/* Hover effect */
.new-message-indicator:hover {
  background-color: #182039;
}

/* Responsive Design */
@media (max-width: 768px) {
  .new-message-indicator {
    bottom: 15px;
    right: 15px;
    padding: 0.5rem;
  }

  .new-message-indicator svg {
    margin-right: 0.3rem;
  }

  .message-count {
    font-size: 0.65rem;
  }
}
</style>
