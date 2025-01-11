<template>
  <div class="h-full h-screen flex flex-col bg-gray-50 dark:bg-gray-800">
    <!-- Header -->
    <div class="header bg-gray-50 dark:bg-gray-800 p-4 flex items-center justify-between rounded-t-lg shadow-md">
      <span class="circle-indicator"></span>
      <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4 font-sans">
        Add Friend
      </h2>

      <button @click="$emit('close')" class="text-2xl hover:text-gray-300 focus:outline-none" aria-label="Close Profile">
        &times;
      </button>
    </div>

    <!-- Sub-Headers for Tabs -->
    <div class="sub-header bg-gray-100 dark:bg-gray-700 flex">
      <button
        :class="activeTab === 'add' ? 'bg-white dark:bg-gray-800 text-[#8a949d] dark:text-white border-b-2 border-[#8a949d] dark:border-white' : 'text-gray-600 dark:text-gray-300'"
        @click="setActiveTab('add')"
        class="flex-1 py-2 px-4 text-center font-sans focus:outline-none transition-colors duration-200"
      >
        Add Users
      </button>
      <button
        :class="activeTab === 'requests' ? 'bg-white dark:bg-gray-800 text-[#8a949d] dark:text-white border-b-2 border-[#8a949d] dark:border-white' : 'text-gray-600 dark:text-gray-300'"
        @click="setActiveTab('requests')"
        class="flex-1 py-2 px-4 text-center font-sans focus:outline-none transition-colors duration-200"
      >
        View Requests
        <span
          v-if="friendshipStore.receivedRequests.length > 0"
          class="text-red-500 dark:text-red-400 font-bold text-lg ml-1"
          title="You have new friend requests"
        >
          {{ friendshipStore.receivedRequests.length }}
        </span>
      </button>
    </div>

    <!-- Content -->
    <div class="sidebar-content bg-white dark:bg-gray-800 flex-1 flex flex-col items-center px-4 py-6 overflow-y-auto">
      <!-- Add Users Tab -->
      <div v-if="activeTab === 'add'" class="w-full">
        <!-- Search Bar -->
        <div class="w-full mb-4 relative">
          <input
            v-model="searchQuery"
            @input="debouncedSearch"
            type="text"
            placeholder="Search users..."
            class="w-full px-4 py-2 pl-10 border border-gray-300 dark:border-gray-600 rounded-full bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#8a949d] font-sans"
          />
          <span class="absolute top-2/4 left-4 -translate-y-2/4 text-gray-400 dark:text-gray-500">
            <!-- Search Icon -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M16 11a5 5 0 11-10 0 5 5 0 0110 0z" />
            </svg>
          </span>
        </div>

        <!-- User List -->
        <div class="w-full flex flex-col">
          <div
            v-for="userItem in users"
            :key="userItem.id"
            class="flex items-center justify-between p-4 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200"
          >
            <div class="flex items-center gap-4">
              <!-- Conditional Avatar -->
              <div
                v-if="userItem.profile && userItem.profile.avatarImage"
                class="relative w-12 h-12 rounded-full overflow-hidden border border-gray-200 dark:border-gray-600"
              >
                <img :src="`/storage/${userItem.profile.avatarImage}`" alt="User Profile" class="w-full h-full object-cover" />
              </div>
              <div
                v-else-if="userItem.profile && userItem.profile.avatarColor"
                :style="{ backgroundColor: userItem.profile.avatarColor || '#ccc' }"
                class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg"
              >
                {{ userItem.name ? userItem.name.charAt(0).toUpperCase() : '?' }}
              </div>
              <div
                v-else
                class="w-12 h-12 rounded-full flex items-center justify-center bg-gray-300 dark:bg-gray-600 text-white font-bold text-lg"
              >
                {{ userItem.name ? userItem.name.charAt(0).toUpperCase() : '?' }}
              </div>

              <!-- User Details -->
              <div class="flex flex-col">
                <span class="text-gray-700 dark:text-gray-200 font-medium font-sans">{{ userItem.name }}</span>
                <span class="text-sm text-gray-500 dark:text-gray-400 font-sans">{{ userItem.profile.bio }}</span>
              </div>
            </div>

            <!-- Add Button -->
            <button
              :disabled="isActionDisabled(userItem.id)"
              @click="sendFriendRequest(userItem.id)"
              class="px-4 py-2 text-sm text-white bg-[#8a949d] dark:bg-gray-700 rounded-full hover:bg-gray-700 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 font-sans flex items-center justify-center disabled:bg-gray-400 dark:disabled:bg-gray-500 disabled:cursor-not-allowed transition-colors duration-200"
              title="Send Friend Request"
              aria-label="Send Friend Request"
            >
              {{ getButtonLabel(userItem.id) }}
              <svg
                v-if="canAdd(userItem.id)"
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 ml-1"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- View Requests Tab -->
      <div v-if="activeTab === 'requests'" class="w-full">
        <!-- ViewRequests uses the store directly -->
        <ViewRequests />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, defineEmits } from "vue";
import debounce from "lodash.debounce";
import { useFriendshipStore } from "@/stores/friendship.js";
import { useToast } from "vue-toastification";
import { useAuthStore } from "@/stores/auth.js";
import ViewRequests from "./ViewRequests.vue";
import axiosInstance from "@/axios.js";

// Emit 'close' event to parent component
const emit = defineEmits(['close']);

// Initialize stores and toast
const friendshipStore = useFriendshipStore();
const authStore = useAuthStore();
const toast = useToast();

// Reactive variables
const searchQuery = ref("");
const users = ref([]);
const activeTab = ref("add");

// Debounced search function to limit API calls
const debouncedSearch = debounce(async () => {
  if (searchQuery.value.trim() === "") {
    users.value = [];
    return;
  }
  try {
    const response = await axiosInstance.get("/friends/search", {
      params: { query: searchQuery.value },
    });

    // Ensure response data is an array
    if (Array.isArray(response.data.data)) {
      users.value = response.data.data;
    } else {
      console.error("Unexpected API response format:", response.data);
      users.value = [];
    }

    // Fetch their friendship statuses if needed
    const userIds = users.value.map(user => user.id);
    if (userIds.length > 0) {
      await friendshipStore.getFriendshipStatuses(userIds);
    }
  } catch (error) {
    console.error("Error searching users:", error);
    toast.error("Failed to search users.");
    users.value = [];
  }
}, 300);

// Function to send a friend request
const sendFriendRequest = async (friendId) => {
  try {
    const response = await axiosInstance.post("/friends/request", { friend_id: friendId });
    toast.success(response.data.message || "Friend request sent.");
    friendshipStore.friendshipStatuses[friendId] = "pending";
  } catch (error) {
    const errorMsg = error.response?.data?.message || "An error occurred.";
    toast.error(errorMsg);
  }
};

// Function to get the label for the Add button based on friendship status
const getButtonLabel = (userId) => {
  const status = friendshipStore.friendshipStatuses?.[userId] || "none";
  return status === "pending" ? "Pending" : status === "accepted" ? "Friends" : "Add";
};

// Function to determine if the Add button should be disabled
const isActionDisabled = (userId) => {
  const status = friendshipStore.friendshipStatuses?.[userId] || "none";
  return status === "pending" || status === "accepted";
};

// Function to check if a user can be added (no existing friendship)
const canAdd = (userId) => {
  const status = friendshipStore.friendshipStatuses?.[userId] || "none";
  return status === "none";
};

// Function to set the active tab
const setActiveTab = (tab) => {
  activeTab.value = tab;
};

// Function to emit the 'close' event
const emitClose = () => {
  emit("close");
};

// Fetch initial friend data on component mount
onMounted(async () => {
  if (authStore.isAuthenticated) {
    await friendshipStore.fetchFriendData();
  }
});
</script>

<style scoped>
/* Header Shadow */
.header {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Sub-Header Button Hover Effects */
.sub-header button:hover {
  /* Optional: Add hover effects if desired */
}

/* Sidebar Content Scroll */
.sidebar-content {
  max-height: calc(100vh - 240px); /* Adjust based on header heights */
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .header {
    flex-direction: column;
    height: auto;
    padding: 1rem;
  }

  .sub-header {
    flex-direction: column;
  }

  .sub-header button {
    border-bottom: none;
    border-right: 2px solid transparent;
  }

  .sub-header button:last-child {
    border-right: none;
  }
}

/* Ensure the main content has padding at the bottom to prevent overlap with sticky footer */
main {
  padding-bottom: 80px; /* Height of the footer */
}

/* Optional: Enhance placeholder text color in dark mode */
input::placeholder,
textarea::placeholder {
  color: #a0aec0; /* Tailwind's gray-400 */
}

.dark input::placeholder,
.dark textarea::placeholder {
  color: #cbd5e0; /* Tailwind's gray-300 */
}
</style>
