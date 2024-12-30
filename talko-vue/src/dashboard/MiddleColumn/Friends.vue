<!-- src/components/Friends.vue -->
<template>
  <div class="friends-list-container flex flex-col h-full">
    <!-- Search Bar -->
    <div class="m-4">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search friends..."
        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-200 dark:text-gray-800"
      />
    </div>

    <!-- Scrollable Content Area -->
    <div class="flex-1 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-200">
      <!-- Loading State -->
      <div v-if="friendshipStore.isLoading" class="flex justify-center items-center h-full">
        <!-- Spinner SVG -->
        <svg
          class="animate-spin h-10 w-10 text-blue-500"
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
        <span class="ml-3 text-gray-600 dark:text-gray-500">Loading friends...</span>
      </div>

      <!-- Error State -->
      <div v-else-if="friendshipStore.error" class="flex justify-center items-center h-full">
        <p class="text-red-500 dark:text-red-400">Error: {{ friendshipStore.error }}</p>
      </div>

      <!-- Friends List -->
      <ul v-else class="space-y-4">
        <li
          v-for="friend in filteredFriends"
          :key="friend.id"
          @click="selectUser(friend)"
          @keydown.enter="selectUser(friend)"
          tabindex="0"
          role="button"
          :aria-pressed="selectedUser?.id === friend.id"
          :class="[
            'flex items-center p-4 rounded-lg shadow-sm transition transform hover:scale-105 hover:shadow-md focus:outline-none focus:ring-2',
            selectedUser?.id === friend.id
              ? 'border-2 border-[#8a949d] bg-[#8a949d]'
              : 'bg-gray-50 dark:bg-white'
          ]"
        >
          <!-- Friend Avatar -->
          <img
            class="w-14 h-14 rounded-full object-cover flex-shrink-0"
            :src="friend.avatar || defaultAvatar"
            :alt="`Avatar of ${friend.name}`"
          />

          <!-- Friend Details -->
          <div class="flex-1 ml-4">
            <h3 class="text-lg font-medium text-gray-800 dark:text-gray-900">{{ friend.name }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-700">{{ friend.email }}</p>
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
  </div>
</template>

<script lang="ts">
import { defineComponent, onMounted, ref, computed } from 'vue';
import { useFriendshipStore } from '@/stores/friendship';
import axiosInstance from '@/axios';

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
  emits: ['select-user'],
  setup(props, { emit }) {
    const friendshipStore = useFriendshipStore();
    const searchQuery = ref('');
    const selectedUser = ref(null);

    onMounted(() => {
      friendshipStore.fetchFriendAcceptedData();
    });

    const defaultAvatar = 'https://readymadeui.com/team-6.webp';

    // Helper method to determine badge classes based on status
    const statusBadgeClass = (status: string) => {
      switch (status) {
        case 'accepted':
          return 'bg-green-100 text-green-800';
        case 'pending':
          return 'bg-yellow-100 text-yellow-800';
        case 'blocked':
          return 'bg-red-100 text-red-800';
        default:
          return 'bg-gray-100 text-gray-800';
      }
    };

    // Method to emit the selected user
    const selectUser = async (friend: { id: number }) => {
      try {
        // Remove '/api' from the URL as it's already included in the baseURL
        const response = await axiosInstance.get(`/friend/${friend.id}`);
        const data = response.data;
        // Update the selected user and emit the event
        selectedUser.value = friend;
        emit('select-user', data);
      } catch (error) {
        console.error('Error fetching user:', error);
      }
    };


    // Computed property for filtered friends based on search query
    const filteredFriends = computed(() => {
      if (!searchQuery.value) {
        return friendshipStore.friends;
      }
      const query = searchQuery.value.toLowerCase();
      return friendshipStore.friends.filter(
        (friend) =>
          friend.name.toLowerCase().includes(query) ||
          friend.email.toLowerCase().includes(query)
      );
    });

    return { friendshipStore, defaultAvatar, statusBadgeClass, selectUser, searchQuery, filteredFriends, selectedUser };
  },
});
</script>

<style scoped>
.friends-list-container {
  height: 100%;
}

.scrollbar-thin::-webkit-scrollbar {
  width: 8px;
}

.scrollbar-thin::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
  background-color: #c1c1c1;
  border-radius: 4px;
  border: 2px solid #f1f1f1;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
  background-color: #a8a8a8;
}

/* Firefox Scrollbar Styling */
.scrollbar-thin {
  scrollbar-width: thin;
  scrollbar-color: #c1c1c1 #f1f1f1;
}
</style>
