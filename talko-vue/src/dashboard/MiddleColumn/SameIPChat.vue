<template>
  <div class="p-6 min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center h-full">
      <svg
        class="animate-spin h-8 w-8 text-blue-500 dark:text-blue-400 mr-2"
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
      <span class="text-blue-500 dark:text-blue-400 text-lg font-semibold">Loading...</span>
    </div>

    <!-- Error State -->
    <div v-if="error" class="flex justify-center items-center h-full">
      <p class="text-red-500 dark:text-red-400 text-lg font-semibold">
        {{ error }}
      </p>
    </div>

    <!-- Main Content -->
    <div v-if="!isLoading && !error">
      <!-- Header -->
      <div class="mb-6 text-center">
        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">
          Current Users in Chat: <span class="text-blue-600 dark:text-blue-400">{{ onlineUsersCount }}</span>
        </h2>
      </div>

      <!-- Users Grid -->
      <div v-if="onlineUsers.length" class=" grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-6">
        <div
          v-for="user in onlineUsers"
          :key="user.id"
          class=" rounded-lg transition-shadow duration-300 p-4 flex flex-col items-center"
        >
          <button
            @click="handleUserClick(user)"
            class="focus:outline-none"
            aria-label="View Profile of {{ user.name }}"
          >
            <div class="relative">
              <!-- Avatar Image or Avatar Color -->
              <div
                v-if="user.avatarImage"
                class="w-20 h-20 rounded-full overflow-hidden border-2 border-gray-300 dark:border-gray-700 shadow-md"
              >
                <img
                  :src="`/storage/${user.avatarImage}`"
                  alt="Avatar"
                  class="w-full h-full object-cover"
                />
              </div>
              <div
                v-else
                :style="{ backgroundColor: user.avatarColor || '#ccc' }"
                class="w-20 h-20 rounded-full flex items-center justify-center text-white text-2xl font-bold border-2 border-gray-300 dark:border-gray-700 shadow-md"
              >
                {{ getInitials(user.name) }}
              </div>

              <!-- Online Status Indicator -->
              <span
                class="absolute bottom-2 right-2 block w-4 h-4 rounded-full bg-green-500 ring-2 ring-white dark:ring-gray-800"
                :title="user.is_online ? 'Online' : 'Offline'"
              ></span>
            </div>
          </button>

          <!-- User Name -->
          <div class="mt-3 text-center">
            <h3
              class="text-lg font-medium text-gray-800 dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-300"
              :title="user.id === currentUserId ? 'This is you' : user.name"
            >
              {{ formatName(user.name) }}
            </h3>
          </div>
        </div>
      </div>

      <!-- No Users Found -->
      <div v-else class="text-center text-gray-500 dark:text-gray-400 text-lg">
        No online users with the same IP found.
      </div>
    </div>
  </div>
</template>


<script lang="ts">
import { defineComponent, onMounted, computed, PropType } from 'vue';
import { useSameIp } from '@/stores/sameIp';

export default defineComponent({
  name: 'SameIpChat',
  props: {
    userProp: {
      type: Object as PropType<{
        id: number;
        name: string;
        email: string;
        profile?: {
          id: number;
          user_id: number;
          ip_address: string;
          is_online: number;
          last_activity: string;
          avatarImage: string;
          avatarColor: string;
          created_at: string;
          updated_at: string;
        };
      }>,
      default: () => ({ id: 0, name: '', email: '' }),
    },
  },
  emits: ['open-profile'],

  setup(props, { emit }) {
    const onlineUsersStore = useSameIp();

    onMounted(() => {
      onlineUsersStore.listenToOnlineUsers();
    });

    const currentUserId = computed(() => props.userProp?.id || 0);

    const handleUserClick = (user: any) => {
      // If the clicked user is the current user:
      if (user.id === currentUserId.value) {
        emit('open-profile', props.userProp);
        return;
      }

      // Otherwise transform and emit the other user’s data
      const transformedUser = {
        id: user.user?.id || user.id,
        name: user.user?.name || user.name,
        email: user.user?.email || '',
        email_verified_at: null,
        created_at: user.created_at || '',
        updated_at: user.updated_at || '',
        profile: {
          id: user.id,
          user_id: user.user?.id || user.id,
          userId: user.userId || '',
          ip_address: user.ip_address || '',
          is_online: user.is_online || 0,
          last_activity: user.last_activity || '',
          avatarImage: user.avatarImage || '',
          avatarColor: user.avatarColor || '',
          bio: user.bio || null,
          created_at: user.created_at || '',
          updated_at: user.updated_at || '',
        },
      };

      emit('open-profile', transformedUser);
    };

    const onlineUsers = computed(() => onlineUsersStore.formattedOnlineUsers);
    const onlineUsersCount = computed(() => onlineUsersStore.onlineUsersCount);
    const isLoading = computed(() => onlineUsersStore.isLoading);
    const error = computed(() => onlineUsersStore.error);

    const getInitials = (name: string): string => {
      if (!name) return '?';
      const names = name.trim().split(' ');
      if (names.length === 1) {
        return names[0].charAt(0).toUpperCase();
      }
      return (names[0].charAt(0) + names[1].charAt(0)).toUpperCase();
    };

    const formatName = (name: string, maxLength: number = 15): string => {
      if (name.length > maxLength) {
        return `${name.slice(0, maxLength - 3)}...`;
      }
      return name;
    };

    return {
      onlineUsers,
      onlineUsersCount,
      isLoading,
      error,
      getInitials,
      formatName,
      currentUserId,
      handleUserClick,
    };
  },
});
</script>
