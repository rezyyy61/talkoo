<template>
  <div class="p-4 flex flex-col w-full h-full">
    <!-- ChatHeader Section -->
    <div v-if="currentView === 'same-ip'" class="mb-4">
      <ChatHeader :user="groupHeader" />
    </div>
    <div
      v-else-if="selectedUser && selectedUser.id !== user.id"
      class="mb-4"
    >
      <ChatHeader :user="selectedUser" @open-profile="toggleUserProfile" />
    </div>
    <div v-else class="mb-4">
      <p class="text-gray-600 dark:text-gray-400">Select a user to view details.</p>
    </div>

    <!-- Chat View Section -->
    <div class="flex-1 overflow-y-auto">
      <ChatView v-if="currentView === 'same-ip' || selectedUser" />
      <div v-else class="flex items-center justify-center h-full">
        <p class="text-gray-600 dark:text-gray-400">Chat view goes here...</p>
      </div>
    </div>

    <!-- Input Section -->
    <div class="mt-4">
      <ChatInput :chatType="chatType" />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, computed } from 'vue';
import ChatHeader from "@/dashboard/RightColumn/ChatHeader.vue";
import ChatInput from "@/dashboard/RightColumn/ChatInput.vue";
import ChatView from "@/dashboard/RightColumn/ChatView.vue";

export default defineComponent({
  name: 'RightColumn',
  components: { ChatHeader, ChatInput, ChatView },
  props: {
    currentView: {
      type: String,
      required: true,
    },
    selectedUser: {
      type: Object,
      default: null,
    },
    user: {
      type: Object,
      required: true,
    },
    groupHeader: {
      type: Object,
      default: null,
    },
  },
  emits: ['toggle-user-profile'],
  setup(props) {
    const chatType = computed(() => (props.currentView === 'same-ip' ? 'group' : 'private'));

    const toggleUserProfile = (user) => {
      props.$emit('toggle-user-profile', user);
    };

    return { chatType, toggleUserProfile };
  },
});
</script>

<style scoped>
/* Removed any background or shadow styles to inherit from parent */
</style>
