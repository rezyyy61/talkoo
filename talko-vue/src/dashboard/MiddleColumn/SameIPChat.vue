<template>
  <div class="p-6 min-h-screen bg-gray-50">
    <div v-if="isLoading" class="text-center text-blue-500 font-semibold text-lg">
      Loading...
    </div>
    <div v-if="error" class="text-center text-red-500 font-semibold text-lg">
      {{ error }}
    </div>

    <div v-if="!isLoading">
      <div class="mb-6 text-center text-gray-700 text-xl font-bold">
        Current Users in Chat: <span class="text-blue-600">{{ onlineUsersCount }}</span>
      </div>

      <div v-if="onlineUsers.length" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <div
          v-for="user in onlineUsers"
          :key="user.id"
          class="flex flex-col items-center p-3 rounded-lg transition"
        >
          <div
            class="relative cursor-pointer hover:scale-105"
            @click="handleUserClick(user)"
          >
            <!-- Display Avatar Image or Avatar Color -->
            <div
              v-if="user.avatarImage"
              class="w-20 h-20 rounded-full overflow-hidden shadow-md border-4 border-gray-500"
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
              class="w-20 h-20 rounded-full flex items-center justify-center text-gray-600 text-xl font-bold border-2 border-gray-300"
            >
              {{ getInitials(user.name) }}
            </div>

            <!-- Online Status Indicator -->
            <span
              class="absolute bottom-1 right-1 block w-3 h-3 rounded-full bg-green-500 ring-2 ring-white"
            ></span>
          </div>
          <div class="mt-2 text-center">
            <h3
              class="text-lg font-medium text-gray-800"
              :title="user.id === currentUserId ? 'This is you' : user.name"
            >
              {{ formatName(user.name) }}
            </h3>
          </div>
        </div>
      </div>

      <div v-else class="text-center text-gray-500 text-lg">
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

    const formatName = (name: string, maxLength: number = 10): string => {
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
