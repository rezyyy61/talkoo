<template>
    <div class=" m-2 flex justify-end items-center">
      <img
        :src="user.avatar || defaultAvatar"
        alt="User Avatar"
        class="w-14 h-14 rounded-full object-cover cursor-pointer"
        @click="$emit('view-profile')"
        title="View Profile"
      />
    </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth.js';
import { onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';

// Path to a default avatar image. Ensure this path is correct or replace it with a valid URL.
const defaultAvatar = 'https://via.placeholder.com/150';

const authStore = useAuthStore();
const router = useRouter();

// Computed property to access user data
const user = computed(() => authStore.user);

// Fetch user data on component mount if not already available
onMounted(() => {
  if (!authStore.user && authStore.isAuthenticated) {
    authStore.fetchUser();
  }
});

</script>

<style scoped>
/* Add any component-specific styles here */
</style>
