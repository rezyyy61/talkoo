<!-- src/components/Friends.vue -->
<template>
  <div class="friends-list-container flex flex-col h-full">
    <!-- Search Bar -->
    <div class="m-4">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search friends..."
        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200"
      />
    </div>

    <!-- Scrollable Content Area -->
    <div class="flex-1 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-700 dark:scrollbar-track-gray-800">
      <!-- Loading State -->
      <div v-if="friendshipStore.isLoading" class="flex justify-center items-center h-full">
        <!-- Spinner SVG -->
        <svg
          class="animate-spin h-10 w-10 text-blue-500 dark:text-blue-400"
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
        <span class="ml-3 text-gray-600 dark:text-gray-400">Loading friends...</span>
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
          @click="selectUser(friend); toggleTooltip(friend.id)"
          @keydown.enter="selectUser(friend); toggleTooltip(friend.id)"
          tabindex="0"
          role="button"
          :aria-pressed="selectedUser?.id === friend.id"
          :class="[
            'relative flex items-center p-4 rounded-lg shadow-sm transition transform hover:scale-105 focus:outline-none focus:ring-2 group',
            selectedUser?.id === friend.id
              ? 'border-2 border-blue-500 bg-blue-500 text-white dark:border-blue-400 dark:bg-blue-400 dark:text-gray-900'
              : 'bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-200',
            isPinned(friend.id) ? 'ring-2 ring-yellow-500' : '',
            'hover:bg-blue-100 dark:hover:bg-blue-900'
          ]"
        >

          <!-- Friend Avatar -->
          <div
            v-if="friend.profile?.avatarImage"
            class="w-14 h-14 rounded-full overflow-hidden flex-shrink-0"
          >
            <img
              :src="`/storage/${friend.profile.avatarImage}`"
              :alt="`Avatar of ${friend.name}`"
              class="w-full h-full object-cover"
              loading="lazy"
            />
          </div>
          <div
            v-else
            :style="{ backgroundColor: friend.profile?.avatarColor || '#ccc' }"
            class="w-14 h-14 rounded-full flex items-center justify-center text-white font-bold text-xl flex-shrink-0"
          >
            {{ friend.name ? friend.name.charAt(0).toUpperCase() : '?' }}
          </div>

          <!-- Friend Details -->
          <div class="flex-1 ml-4 flex flex-col">
            <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">{{ friend.name }}</h3>
            <p
              :class="[
                'text-sm',
                selectedUser?.id === friend.id
                  ? 'text-white dark:text-gray-900'
                  : 'text-gray-600 dark:text-gray-400'
              ]"
            >
              {{ friend.email }}
            </p>
          </div>

          <!-- Star Icon for Pinning -->
          <div class="ml-4">
            <svg
              @click.stop="togglePin(friend.id)"
              xmlns="http://www.w3.org/2000/svg"
              :class="[
                'w-6 h-6 cursor-pointer transition-colors',
                isPinned(friend.id)
                  ? 'text-blue-500'
                  : 'text-gray-300 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-300'
              ]"
              fill="currentColor"
              viewBox="0 0 24 24"
              aria-label="Pin Friend"
              role="button"
              tabindex="0"
            >
              <path
                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l2.036 6.273a1 1 0 00.95.69h6.573c.969 0 1.371 1.24.588 1.81l-5.324 3.872a1 1 0 00-.364 1.118l2.036 6.273c.3.921-.755 1.688-1.54 1.118l-5.324-3.872a1 1 0 00-1.176 0l-5.324 3.872c-.784.57-1.838-.197-1.54-1.118l2.036-6.273a1 1 0 00-.364-1.118l-5.324-3.872c-.783-.57-.38-1.81.588-1.81h6.573a1 1 0 00.95-.69l2.036-6.273z"
              />
            </svg>
          </div>

          <!-- Tooltip for Friend Names -->
          <div
            v-if="showTooltip[friend.id]"
            class="absolute left-full top-1/2 transform -translate-y-1/2 bg-gray-700 dark:bg-gray-600 text-white text-sm rounded py-1 px-2 opacity-100 transition-opacity duration-300 z-10 whitespace-nowrap"
          >
            {{ friend.name }}
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, onMounted, ref, computed, onBeforeUnmount } from 'vue';
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
    const showTooltip = ref<{ [key: number]: boolean }>({});
    const pinnedFriends = ref<Set<number>>(new Set());

    onMounted(() => {
      friendshipStore.fetchFriendAcceptedData();

      const storedPinned = localStorage.getItem('pinnedFriends');
      if (storedPinned) {
        pinnedFriends.value = new Set(JSON.parse(storedPinned));
      }
    });

    const selectUser = async (friend: { id: number }) => {
      try {
        const response = await axiosInstance.get(`/friend/${friend.id}`);
        const data = response.data;
        selectedUser.value = friend;
        emit('select-user', data);
        showTooltip.value[friend.id] = false;
      } catch (error) {
        console.error('Error fetching user:', error);
      }
    };

    const filteredFriends = computed(() => {
      let friends = [...friendshipStore.friends];

      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        friends = friends.filter(
          (friend) =>
            friend.name.toLowerCase().includes(query) ||
            friend.email.toLowerCase().includes(query)
        );
      }

      return friends.sort((a, b) => {
        const aPinned = isPinned(a.id) ? 1 : 0;
        const bPinned = isPinned(b.id) ? 1 : 0;
        return bPinned - aPinned;
      });
    });

    const toggleTooltip = (friendId: number) => {
      showTooltip.value[friendId] = !showTooltip.value[friendId];
    };

    const togglePin = (friendId: number) => {
      if (pinnedFriends.value.has(friendId)) {
        pinnedFriends.value.delete(friendId);
      } else {
        pinnedFriends.value.add(friendId);
      }
      localStorage.setItem('pinnedFriends', JSON.stringify(Array.from(pinnedFriends.value)));
    };

    const isPinned = (friendId: number) => {
      return pinnedFriends.value.has(friendId);
    };

    return {
      friendshipStore,
      selectUser,
      searchQuery,
      filteredFriends,
      selectedUser,
      showTooltip,
      toggleTooltip,
      togglePin,
      isPinned,
    };
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

.scrollbar-thin {
  scrollbar-width: thin;
  scrollbar-color: #c1c1c1 #f1f1f1;
}

li {
  position: relative;
}

.tooltip {
  z-index: 10;
  white-space: nowrap;
}
</style>
