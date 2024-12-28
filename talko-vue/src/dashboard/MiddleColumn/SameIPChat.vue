<template>
  <div class="p-6 min-h-screen bg-gray-50">
    <div v-if="isLoading" class="text-center text-blue-500 font-semibold text-lg">Loading...</div>
    <div v-if="error" class="text-center text-red-500 font-semibold text-lg">{{ error }}</div>

    <div v-if="!isLoading">
      <div class="mb-6 text-center text-gray-700 text-xl font-bold">
        Current Users in Chat: <span class="text-blue-600">{{ onlineUsersCount }}</span>
      </div>

      <div v-if="onlineUsers.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-1">
        <div
          v-for="user in onlineUsers"
          :key="user.id"
          class="flex flex-col items-center p-3 transition cursor-pointer"
        >
          <div class="relative">
            <img
              v-if="!isColorCode(user.avatar)"
              :src="user.avatar"
              alt="Avatar"
              class="w-20 h-20 rounded-full object-cover border-2 border-gray-300 hover:border-gray-500"
            />
            <div
              v-else
              :style="{ backgroundColor: user.avatar }"
              class="size-14 rounded-full flex items-center justify-center text-gray-600 text-xl font-bold border-2 border-gray-300 hover:border-gray-500"
            >
              {{ getInitials(user.name) }}
            </div>

            <span class="absolute bottom-1 right-1 block size-3 rounded-full bg-green-500 ring-2 ring-white"></span>
          </div>
          <div class="mt-1 text-center">
            <h3 class="text-lg font-medium text-gray-800" :title="user.name">
              {{ formatName(user.name) }}
            </h3>
          </div>
        </div>
      </div>

      <div v-else class="text-center text-gray-500 text-lg">No online users with the same IP found.</div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, onMounted, computed } from 'vue';
import { useSameIp } from '@/stores/sameIp';

export default defineComponent({
  name: 'SameIpChat',
  setup() {
    const onlineUsersStore = useSameIp();

    onMounted(() => {
      onlineUsersStore.listenToOnlineUsers();
    });

    const onlineUsers = computed(() => onlineUsersStore.formattedOnlineUsers);
    const onlineUsersCount = computed(() => onlineUsersStore.onlineUsersCount);
    const isLoading = computed(() => onlineUsersStore.isLoading);
    const error = computed(() => onlineUsersStore.error);

    const isColorCode = (avatar: string) => {
      const colorCodeRegex = /^#([0-9A-F]{3}){1,2}$/i;
      return colorCodeRegex.test(avatar);
    };

    const getInitials = (name: string) => {
      if (!name) return '';
      const names = name.trim().split(' ');
      if (names.length === 1) {
        return names[0].charAt(0).toUpperCase();
      }
      return (names[0].charAt(0) + names[1].charAt(0)).toUpperCase();
    };

    const formatName = (name: string, maxLength: number = 10) => {
      if (name.length > maxLength) {
        return `${name.slice(0, maxLength - 3)}.`;
      }
      return name;
    };

    return {
      onlineUsers,
      onlineUsersCount,
      isLoading,
      error,
      isColorCode,
      getInitials,
      formatName,
    };
  },
});
</script>

<style scoped>
body {
  background-color: #f9fafb;
  font-family: 'Inter', sans-serif;
}
</style>
