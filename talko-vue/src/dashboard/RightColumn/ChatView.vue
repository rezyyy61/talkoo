<!-- ChatView.vue -->
<template>
  <div
    class="chatView flex-1 p-4 overflow-y-auto w-full h-full flex flex-col space-y-4 relative"
    ref="messagesContainer"
    @scroll="handleScroll"
  >
    <!-- Loading State -->
    <div v-if="messageStore.isLoading" class="flex justify-center items-center">
      <!-- Spinner SVG -->
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
        :isBlurred="selectedMessageId && message.id !== selectedMessageId"
        @reaction="handleReaction"
        @message-clicked="openModal"
        @message-right-clicked="handleMessageRightClick"
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
      <img
        alt="Receiver Avatar"
        class="w-10 h-10 rounded-full mr-2"
        :src="getReceiverAvatar()"
      />
      <span class="dot" style="background-color: #2563eb;"></span>
      <span class="dot" style="background-color: #2563eb;"></span>
      <span class="dot" style="background-color: #2563eb;"></span>
    </div>

    <!-- New Message Indicator -->
    <div
      v-if="showNewMessageIndicator"
      class="new-message-indicator"
      @click="scrollToBottom"
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
      <span>New Messages</span>
    </div>

    <!-- Context Menu (placed at the end to ensure it's rendered on top) -->
    <ContextMenu
      :visible="contextMenu.visible"
      :position="contextMenu.position"
      @action="handleContextMenuAction"
      @close="closeContextMenu"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent, computed, watch, ref, onMounted, nextTick, onBeforeUnmount } from 'vue';
import { useMessageStore } from '@/stores/message';
import { useAuthStore } from '@/stores/auth';
import ChatBubble from "@/dashboard/RightColumn/ChatBubble.vue";
import ContextMenu from "@/dashboard/RightColumn/ContextMenu.vue";

export default defineComponent({
  name: 'ChatView',
  components: { ChatBubble, ContextMenu },
  setup() {
    const isModalOpen = ref(false);
    const selectedMessage = ref(null);
    const selectedMessageId = ref<number | null>(null);

    const contextMenu = ref({
      visible: false,
      position: { x: 0, y: 0 },
      message: null as any,
    });

    const messageStore = useMessageStore();
    const authStore = useAuthStore();

    const currentUserId = computed(() => authStore.user?.id || null);

    const messagesContainer = ref<HTMLElement | null>(null);
    const showNewMessageIndicator = ref(false);

    let isUserAtBottom = true;

    const scrollToBottom = () => {
      if (messagesContainer.value) {
        messagesContainer.value.scrollTo({
          top: messagesContainer.value.scrollHeight,
          behavior: 'smooth'
        });
        showNewMessageIndicator.value = false;
        isUserAtBottom = true;
      }
    };

    const handleScroll = () => {
      if (!messagesContainer.value) return;

      const { scrollTop, scrollHeight, clientHeight } = messagesContainer.value;
      const threshold = 50;

      if (scrollHeight - scrollTop - clientHeight < threshold) {
        isUserAtBottom = true;
        showNewMessageIndicator.value = false;

        messageStore.markAsRead();
      } else {
        isUserAtBottom = false;
      }
    };

    watch(
      () => messageStore.messages,
      async (newMessages, oldMessages) => {
        await nextTick();
        if (isUserAtBottom) {
          scrollToBottom();
        } else {
          showNewMessageIndicator.value = true;
        }
      }
    );

    watch(
      () => messageStore.receiverId,
      async (newReceiverId, oldReceiverId) => {
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
          messageStore.setConversationId(newConversationId)
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

      // Add keydown listener for Escape key to clear selection and close context menu
      document.addEventListener('keydown', handleKeyDown);
    });

    onBeforeUnmount(() => {
      // Clean up the keydown listener
      document.removeEventListener('keydown', handleKeyDown);
    });

    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        selectedMessageId.value = null;
        closeContextMenu();
      }
    };

    const handleReaction = ({ messageId, emoji }) => {
      const message = messageStore.messages.find(msg => msg.id === messageId);
      if (message) {
        if (!message.reactions) {
          message.reactions = [];
        }
        message.reactions.push({ emoji });
      }
    };

    const getReceiverAvatar = () => {
      // Implement logic to get the receiver's avatar
      // For example:
      const receiver = messageStore.getReceiver();
      return receiver?.profile?.avatarImage ? `/${receiver.profile.avatarImage}` : '/default-avatar.png';
    };

    // Handler for right-click on a message
    const handleMessageRightClick = ({ message, position }) => {
      selectedMessageId.value = message.id;
      contextMenu.value = {
        visible: true,
        position: adjustPosition(position),
        message,
      };
    };

    // Adjust position to prevent overflow (optional)
    const adjustPosition = ({ x, y }) => {
      const menuWidth = 260; // same as .dropdown-menu min-width
      const menuHeight = 400; // approximate height, adjust as needed
      const padding = 10; // padding from the viewport edges

      const viewportWidth = window.innerWidth;
      const viewportHeight = window.innerHeight;

      let adjustedX = x;
      let adjustedY = y;

      if (x + menuWidth + padding > viewportWidth) {
        adjustedX = viewportWidth - menuWidth - padding;
      }

      if (y + menuHeight + padding > viewportHeight) {
        adjustedY = viewportHeight - menuHeight - padding;
      }

      return { x: adjustedX, y: adjustedY };
    };

    // Handler for context menu actions
    const handleContextMenuAction = (action: string) => {
      const message = contextMenu.value.message;
      switch (action) {
        case 'reply':
          handleReply(message);
          break;
        case 'reply_all':
          // Implement reply all logic
          console.log('Reply All to:', message);
          break;
        case 'forward':
          // Implement forward logic
          console.log('Forward:', message);
          break;
        case 'resend':
          // Implement resend logic
          console.log('Resend:', message);
          break;
        case 'copy_conversation':
          // Implement copy conversation logic
          console.log('Copy Conversation:', message);
          break;
        case 'archive_conversation':
          // Implement archive conversation logic
          console.log('Archive Conversation:', message);
          break;
        case 'move_to_folder':
          // Implement move to folder logic
          console.log('Move to Folder:', message);
          break;
        case 'mark_as_important':
          // Implement mark as important logic
          console.log('Mark as Important:', message);
          break;
        case 'mute_notifications':
          // Implement mute notifications logic
          console.log('Mute Notifications:', message);
          break;
        case 'mark_as_unread':
          // Implement mark as unread logic
          console.log('Mark as Unread:', message);
          break;
        case 'mark_as_read':
          // Implement mark as read logic
          console.log('Mark as Read:', message);
          break;
        case 'archive':
          // Implement archive logic
          console.log('Archive:', message);
          break;
        case 'delete':
          // Implement delete logic
          console.log('Delete:', message);
          break;
        case 'report_spam':
          // Implement report spam logic
          console.log('Report Spam:', message);
          break;
        case 'export_conversation':
          // Implement export conversation logic
          console.log('Export Conversation:', message);
          break;
        default:
          console.log('Unknown action:', action);
      }
    };

    const closeContextMenu = () => {
      contextMenu.value.visible = false;
      contextMenu.value.message = null;
    };

    // Optional: Click outside to clear the selection and close context menu
    const handleClickOutside = (event: MouseEvent) => {
      if (messagesContainer.value && !messagesContainer.value.contains(event.target as Node)) {
        selectedMessageId.value = null;
        closeContextMenu();
      }
    };

    watch(contextMenu, (newVal) => {
      if (newVal.visible) {
        document.addEventListener('click', handleClickOutside);
      } else {
        document.removeEventListener('click', handleClickOutside);
      }
    });

    return {
      messageStore,
      currentUserId,
      messagesContainer,
      showNewMessageIndicator,
      scrollToBottom,
      handleScroll,
      handleReaction,
      isModalOpen,
      selectedMessage,
      getReceiverAvatar,
      handleMessageRightClick,
      selectedMessageId,
      contextMenu,
      handleContextMenuAction,
      closeContextMenu,
    };
  },
});
</script>

<style scoped>
.chatView {
  position: relative;
}

/* Typing Indicator */
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

/* New Message Indicator */
.new-message-indicator {
  position: fixed; /* Fixed to viewport */
  bottom: 70px;
  left: 50%;
  transform: translateX(-50%);
  background-color: #2563eb;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 9999px;
  display: flex;
  align-items: center;
  cursor: pointer;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  z-index: 40; /* Below ContextMenu's z-50 */
}
.new-message-indicator svg {
  margin-right: 0.5rem;
}
.new-message-indicator:hover {
  background-color: #1d4ed8;
}

/* Transition Styles */
.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s;
}
.modal-enter-from, .modal-leave-to {
  opacity: 0;
}
.modal-enter-to, .modal-leave-from {
  opacity: 1;
}

/* Context Menu Styles */
.dropdown-menu {
  min-width: 260px;
  animation: fadeIn 0.2s ease-in-out;
}

.dropdown-item {
  border-radius: 0.25rem;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
