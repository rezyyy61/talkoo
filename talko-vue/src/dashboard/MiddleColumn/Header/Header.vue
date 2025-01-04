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
          <line x1="16" y1="11" x2="22" y2="11" stroke="currentColor" stroke-width="2" />
          <line x1="19" y1="8" x2="19" y2="14" stroke="currentColor" stroke-width="2" />
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
        <div
          v-if="user.profile.avatarImage"
          class="relative w-16 h-16 rounded-full overflow-hidden shadow-md border-4 border-white cursor-pointer hover:scale-105 transition-transform"
          @click="viewProfile"
          title="View Profile"
        >
          <img
            :src="`/storage/${user.profile.avatarImage}`"
            alt="User Avatar"
            class="w-full h-full object-cover"
          />
        </div>
        <div
          v-else
          :style="{ backgroundColor: user.profile.avatarColor }"
          class="w-16 h-16 rounded-full flex items-center justify-center text-white font-bold shadow-md border-4 border-white cursor-pointer hover:scale-105 transition-transform"
          @click="viewProfile"
          title="View Profile"
        >
          {{ user.name ? user.name.charAt(0).toUpperCase() : '?' }}
        </div>
      </div>

    </div>

    <!-- Notification Container -->
    <div class="fixed top-5 right-5 flex flex-col gap-2 z-50">
      <div
        v-for="(notification, index) in notifications"
        :key="index"
        class="flex items-center p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400 transition-opacity duration-300"
      >
        <svg
          class="flex-shrink-0 inline w-4 h-4 me-3"
          aria-hidden="true"
          xmlns="http://www.w3.org/2000/svg"
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path
            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"
          />
        </svg>
        <div>
          <span class="font-medium">{{ notification.title }}</span> {{ notification.message }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { useFriendshipStore } from "@/stores/friendship.js";
import { useAuthStore } from "@/stores/auth.js";
import debounce from "lodash.debounce";


const emit = defineEmits(["view-profile", "add-user"]);
const friendshipStore = useFriendshipStore();
const authStore = useAuthStore();
const user = computed(() => authStore.user);
const receivedRequestsCount = computed(() => friendshipStore.receivedRequests.length);
const notifications = ref([]);
const searchQuery = ref("");

const handleSearch = debounce(async () => {
  if (searchQuery.value.trim() === "") {
  }
}, 300);

const openAddUserPanel = () => {
  emit("add-user");
};

const viewProfile = () => {
  emit("view-profile", user.value);
};

const addNotification = (title, message) => {
  const id = Date.now();
  notifications.value.push({ id, title, message });

  setTimeout(() => {
    notifications.value = notifications.value.filter((notif) => notif.id !== id);
  }, 6000);
};

</script>
