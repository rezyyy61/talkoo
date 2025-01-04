<template>
  <div class="user-header p-4 bg-[#8a949d] shadow rounded-lg flex items-center">
    <!-- Avatar Section -->
    <div class="relative flex-shrink-0 cursor-pointer" @click="openProfile">
      <!-- Display Avatar Image or Avatar Color -->
      <div
        v-if="user.profile?.avatarImage"
        class="w-20 h-20 rounded-full overflow-hidden border-2 border-gray-300 hover:border-gray-500"
      >
        <img
          :src="`/storage/${user.profile.avatarImage}`"
          alt="Avatar"
          class="w-full h-full object-cover"
        />
      </div>
      <div
        v-else
        :style="{ backgroundColor: user.profile?.avatarColor || '#ccc' }"
        class="w-20 h-20 rounded-full flex items-center justify-center text-white text-xl font-bold border-2 border-gray-300 hover:border-gray-500"
      >
        {{ getInitials(user.name) }}
      </div>

      <!-- Online Indicator -->
      <span
        class="absolute bottom-1 right-1 block w-3 h-3 rounded-full"
        :class="{
          'bg-green-500': user.profile?.is_online,
          'bg-gray-400': !user.profile?.is_online,
        }"
      ></span>
    </div>

    <!-- Details Section -->
    <div class="ml-4 flex-1">
      <h2 class="text-xl font-bold text-gray-800 dark:text-white">{{ user.name }}</h2>

      <!-- Typing Indicator -->
      <p v-if="typingUsers.length > 0" class="text-sm text-gray-600 dark:text-white">
        <span v-for="(typingUser, index) in typingUsers" :key="typingUser.id">
          {{ typingUser.name }}<span v-if="index < typingUsers.length - 1">, </span>
        </span>
        <span v-if="typingUsers.length === 1"> is</span>
        <span v-else>are</span> typing...
      </p>

      <p v-else-if="user.profile?.is_online" class="text-sm text-green-600 dark:text-green-400">
        Online
      </p>
      <p v-else class="text-sm text-gray-600 dark:text-white">
        {{ getLastActivity(user.profile?.last_activity) }}
      </p>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, computed, PropType } from 'vue';
import { useMessageStore } from '@/stores/message.js';

export default defineComponent({
  name: 'ChatHeader',
  props: {
    user: {
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
      required: true,
    },
  },
  emits: ['open-profile'],

  setup(props, { emit }) {
    const messageStore = useMessageStore();

    const typingUsers = computed(() => messageStore.typingUsers);

    const openProfile = () => {
      emit('open-profile', props.user);
    };

    return {
      typingUsers,
      openProfile,
    };
  },
  methods: {
    getInitials(name: string): string {
      if (!name) return '?';
      const names = name.trim().split(' ');
      if (names.length === 1) {
        return names[0].charAt(0).toUpperCase();
      }
      return (names[0].charAt(0) + names[1].charAt(0)).toUpperCase();
    },
    getLastActivity(dateString: string): string {
      if (!dateString) return 'Unknown time';

      const now = new Date();
      const activityDate = new Date(dateString);

      const diffInMilliseconds = now.getTime() - activityDate.getTime();
      const diffInMinutes = Math.floor(diffInMilliseconds / (1000 * 60));
      const diffInHours = Math.floor(diffInMilliseconds / (1000 * 60 * 60));
      const diffInDays = Math.floor(diffInMilliseconds / (1000 * 60 * 60 * 24));

      if (diffInMinutes < 1) {
        return 'Just now';
      } else if (diffInMinutes < 60) {
        return `${diffInMinutes} minutes ago`;
      } else if (diffInHours < 24) {
        return `${diffInHours} hours ago`;
      } else if (diffInDays === 1) {
        return 'Yesterday';
      } else if (diffInDays < 7) {
        return `${diffInDays} days ago`;
      } else if (diffInDays < 30) {
        return `${Math.floor(diffInDays / 7)} weeks ago`;
      } else {
        return activityDate.toLocaleDateString(undefined, {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
        });
      }
    },
  },
});
</script>

<style scoped>
.user-header {
  display: flex;
  align-items: center;
  background-color: #8a949d;
  padding: 1rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  border-radius: 0.5rem;
}

.avatar {
  display: flex;
  align-items: center;
  justify-content: center;
}

.ml-4 {
  margin-left: 1rem;
}

.text-sm {
  font-size: 0.875rem;
}

.cursor-pointer {
  cursor: pointer;
}
</style>
