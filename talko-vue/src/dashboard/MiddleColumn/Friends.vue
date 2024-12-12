<!-- src/components/Friends.vue -->
<template>
  <div class="friends-list w-full self-stretch p-4">
    <!-- Loading State -->
    <div v-if="friendshipStore.isLoading" class="flex justify-center items-center h-full">
      <svg
        class="animate-spin h-8 w-8 text-gray-500"
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
      <span class="ml-2 text-gray-500 dark:text-gray-400">Loading friends...</span>
    </div>

    <!-- Error State -->
    <div v-else-if="friendshipStore.error" class="flex justify-center items-center h-full">
      <p class="text-red-500 dark:text-red-400">Error: {{ friendshipStore.error }}</p>
    </div>

    <!-- Friends List -->
    <ul v-else class="space-y-1 w-full">
      <li
        v-for="friend in friendshipStore.friends"
        :key="friend.id"
        class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 flex items-center space-x-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 w-full"
      >
        <!-- Friend Avatar -->
        <img
          class="w-12 h-12 rounded-full object-cover flex-shrink-0"
          :src="friend.avatar || defaultAvatar"
          :alt="`Avatar of ${friend.name}`"
        />

        <!-- Friend Details -->
        <div class="flex-1">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white truncate">
            {{ friend.name }}
          </h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
            {{ friend.email }}
          </p>
        </div>

        <!-- Friendship Status -->
        <div>
          <span
            :class="statusBadgeClass(friendshipStore.friendshipStatuses[friend.id])"
            class="px-3 py-1 rounded-full text-xs font-medium"
          >
            {{ friendshipStore.friendshipStatuses[friend.id] || 'None' }}
          </span>
        </div>
      </li>
    </ul>
  </div>
</template>

<script lang="ts">
import { defineComponent, onMounted } from 'vue';
import { useFriendshipStore } from '@/stores/friendship';

export default defineComponent({
  name: 'Friends',
  props: {
    userId: {
      type: Number,
      required: true,
    },
    userName: {
      type: String,
      required: true,
    },
  },
  setup() {
    const friendshipStore = useFriendshipStore();

    onMounted(() => {
      friendshipStore.fetchFriendAcceptedData();
    });

    const defaultAvatar = 'https://readymadeui.com/team-6.webp'; // Ensure this path points to your default avatar image

    // Helper method to determine badge classes based on status
    const statusBadgeClass = (status: string) => {
      switch (status) {
        case 'accepted':
          return 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100';
        case 'pending':
          return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100';
        case 'blocked':
          return 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100';
        default:
          return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-100';
      }
    };

    return { friendshipStore, defaultAvatar, statusBadgeClass };
  },
});
</script>

<style scoped>
.friends-list {
  /* Optional: Add custom styles if needed */
}
</style>
