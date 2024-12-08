<template>
  <div class="flex items-center justify-between p-2 relative">
    <!-- Left: Branding with Random Logo -->
    <div class="flex items-center gap-3">
      <img
        src="/Echo.png"
        alt="Random Logo"
        class="h-14 w-14 object-cover rounded-full"
      />
    </div>

    <!-- Center: Search Bar -->
    <div class="flex-1 flex justify-center">
      <div class="relative w-full max-w-lg">
        <input
          type="text"
          class="w-full py-2 pl-10 pr-4 rounded-md bg-gray-100 shadow-inner focus:bg-white focus:outline-none text-gray-600"
          placeholder="Search anything..."
          v-model="searchQuery"
          @input="handleSearch"
        />
        <svg
          class="absolute left-3 top-2.5 h-5 w-5 text-gray-500"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <circle cx="11" cy="11" r="8" stroke-width="2"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65" stroke-width="2"></line>
        </svg>
      </div>
    </div>

    <!-- Right: Modern Icons and User Profile -->
    <div class="flex items-center gap-4">
      <div class="flex items-center gap-6 relative">
        <!-- Simple User Plus Icon -->
        <svg
          @click="openAddUserPanel"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          class="w-10 h-10 text-gray-600 cursor-pointer hover:text-gray-800 transition"
          title="Add Friend"
        >
          <circle cx="9" cy="8" r="4" fill="none" stroke="currentColor" stroke-width="2" />
          <path
            d="M4 20c0-3 4-5 9-5s9 2 9 5"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          />
          <line
            x1="16"
            y1="11"
            x2="22"
            y2="11"
            stroke="currentColor"
            stroke-width="2"
          />
          <line
            x1="19"
            y1="8"
            x2="19"
            y2="14"
            stroke="currentColor"
            stroke-width="2"
          />
        </svg>
        <!-- Badge for Received Requests -->
        <span
          v-if="receivedRequestsCount > 0"
          class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
          title="You have new friend requests"
        >
          {{ receivedRequestsCount }}
        </span>
      </div>

      <div class="ml-4">
        <img
          :src="user.profile_image_url || 'https://readymadeui.com/team-6.webp'"
          alt="User Avatar"
          class="w-16 h-16 bg-gray-200 rounded-full border-4 border-white shadow-md cursor-pointer hover:scale-105 transition-transform"
          @click="viewProfile"
          title="View Profile"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, defineEmits } from "vue";
import { useFriendshipStore } from "@/stores/friendship.js";
import { useAuthStore } from "@/stores/auth.js";
import { useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import debounce from "lodash.debounce";

const emit = defineEmits(['view-profile', 'add-user']);

const friendshipStore = useFriendshipStore();
const authStore = useAuthStore();
const router = useRouter();
const toast = useToast();

const user = computed(() => authStore.user);
const receivedRequestsCount = computed(() => friendshipStore.receivedRequests.length);

// Local state for search
const searchQuery = ref("");

// Debounced search handler
const handleSearch = debounce(async () => {
  if (searchQuery.value.trim() === "") {
    // ... handle empty search if needed
  }
}, 300);

// Emit event to open Add User Panel
const openAddUserPanel = () => {
  emit('add-user');
};

// Emit event to open User Profile Panel
const viewProfile = () => {
  emit('view-profile');
};

// Fetch friendship data on component mount
onMounted(async () => {
  if (authStore.isAuthenticated) {
    await friendshipStore.fetchFriendData();
  }
});
</script>

<style scoped>
/* Additional hover effects, if needed */
</style>
