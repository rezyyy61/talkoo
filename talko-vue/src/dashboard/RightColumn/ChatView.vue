<template>
  <div
    class="chatView flex-1 p-4 overflow-y-auto w-full h-full flex flex-col space-y-4"
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
        :src="defaultAvatar"
        alt="Receiver Avatar"
        class="w-10 h-10 rounded-full mr-2"
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
  </div>
</template>

<script lang="ts">
import { defineComponent, computed, watch, ref, onMounted, nextTick } from 'vue';
import { useMessageStore } from '@/stores/message';
import { useAuthStore } from '@/stores/auth';
import ChatBubble from "@/dashboard/RightColumn/ChatBubble.vue";

export default defineComponent({
  name: 'ChatView',
  components: { ChatBubble },
  setup() {
    const messageStore = useMessageStore();
    const authStore = useAuthStore();

    const currentUserId = computed(() => authStore.user?.id || null);

    const messagesContainer = ref<HTMLElement | null>(null);
    const showNewMessageIndicator = ref(false);
    const defaultAvatar = 'https://readymadeui.com/team-6.webp';

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
    });

    return {
      messageStore,
      currentUserId,
      messagesContainer,
      showNewMessageIndicator,
      defaultAvatar,
      scrollToBottom,
      handleScroll,
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
  position: absolute;
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
}
.new-message-indicator svg {
  margin-right: 0.5rem;
}
.new-message-indicator:hover {
  background-color: #1d4ed8;
}
</style>
