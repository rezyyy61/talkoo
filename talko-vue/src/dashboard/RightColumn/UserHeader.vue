<!-- src/dashboard/RightColumn/UserHeader.vue -->
<template>
  <div class="user-header p-4 bg-[#8a949d] shadow rounded-lg flex items-center space-x-4">
    <img
      class="w-16 h-16 rounded-full object-cover"
      :src="user.avatar || defaultAvatar"
      :alt="`Avatar of ${user.name}`"
    />
    <div>
      <h2 class="text-xl font-bold text-gray-800 dark:text-white">{{ user.name }}</h2>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'UserHeader',
  props: {
    user: {
      type: Object as () => { id: number; name: string; email: string; avatar?: string },
      required: true,
    },
  },
  data() {
    return {
      defaultAvatar: 'https://readymadeui.com/team-6.webp', // Same default as Friends.vue
    };
  },
});
</script>

<style scoped>
.user-header {
  /* Optional: Add custom styles if needed */
}
</style>
