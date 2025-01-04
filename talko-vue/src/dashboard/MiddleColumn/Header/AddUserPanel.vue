<template>
  <div>
    <div class="middle-header bg-[#8a949d] h-[120px] flex items-center justify-between px-6 text-white rounded-t-xl">
      <span class="circle-indicator"></span>
      <h2 class="text-2xl font-extrabold text-white tracking-wide capitalize ml-2">
        Add Friend
      </h2>

      <button @click="emitClose" class="text-xl hover:text-gray-300">&times;</button>
    </div>

    <!-- Sub-Headers for Tabs -->
    <div class="sub-header bg-[#f3f4f6] flex">
      <button
        :class="activeTab === 'add' ? 'bg-white text-[#8a949d] border-b-2 border-[#8a949d]' : 'text-gray-600'"
        @click="setActiveTab('add')"
        class="flex-1 py-2 px-4 text-center font-sans focus:outline-none">
        Add Users
      </button>
      <button
        :class="activeTab === 'requests' ? 'bg-white text-[#8a949d] border-b-2 border-[#8a949d]' : 'text-gray-600'"
        @click="setActiveTab('requests')"
        class="flex-1 py-2 px-4 text-center font-sans focus:outline-none">
        View Requests
        <span
          v-if="friendshipStore.receivedRequests.length > 0"
          class="text-red-500 font-bold text-lg"
          title="You have new friend requests">
          {{ friendshipStore.receivedRequests.length }}
        </span>
      </button>
    </div>

    <!-- Content -->
    <div class="sidebar-content bg-white flex-1 flex flex-col items-center px-4 py-6 overflow-y-auto">
      <!-- Add Users Tab -->
      <div v-if="activeTab === 'add'" class="w-full">
        <!-- Search Bar -->
        <div class="w-full mb-4 relative">
          <input
            v-model="searchQuery"
            @input="debouncedSearch"
            type="text"
            placeholder="Search users..."
            class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-[#8a949d] font-sans" />
          <span class="absolute top-2/4 left-4 -translate-y-2/4 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16l-4-4m0 0l4-4m-4 4h16" />
            </svg>
          </span>
        </div>

        <!-- User List -->
        <div class="w-full flex flex-col">
          <div
            v-for="userItem in users"
            :key="userItem.id"
            class="flex items-center justify-between p-4 border-b border-gray-100 hover:bg-gray-50">
            <div class="flex items-center gap-4">
              <!-- Conditional Avatar -->
              <div
                v-if="userItem.profile && userItem.profile.avatarImage"
                class="relative w-12 h-12 rounded-full overflow-hidden border border-gray-200">
                <img :src="`/storage/${userItem.profile.avatarImage }`" alt="User Profile" class="w-full h-full object-cover" />
              </div>
              <div
                v-else-if="userItem.profile && userItem.profile.avatarColor"
                :style="{ backgroundColor: userItem.profile.avatarColor || '#ccc' }"
                class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold">
                {{ userItem.name ? userItem.name.charAt(0).toUpperCase() : '?' }}
              </div>
              <div
                v-else
                class="w-12 h-12 rounded-full flex items-center justify-center bg-gray-300 text-white font-bold">
                {{ userItem.name ? userItem.name.charAt(0).toUpperCase() : '?' }}
              </div>

              <!-- User Details -->
              <div class="flex flex-col">
                <span class="text-gray-700 font-medium font-sans">{{ userItem.name }}</span>
                <span class="text-sm text-gray-500 font-sans">{{ userItem.profile.last_activity }}</span>
              </div>
            </div>

            <!-- Add Button -->
            <button
              :disabled="isActionDisabled(userItem.id)"
              @click="sendFriendRequest(userItem.id)"
              class="px-4 py-2 text-sm text-white bg-[#8a949d] rounded-full hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 font-sans flex items-center justify-center">
              {{ getButtonLabel(userItem.id) }}
              <svg
                v-if="canAdd(userItem.id)"
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 ml-1"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- View Requests Tab -->
      <div v-if="activeTab === 'requests'" class="w-full bg-primary-500">
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

const emit = defineEmits(['close']);

const friendshipStore = useFriendshipStore();
const authStore = useAuthStore();
const toast = useToast();

const searchQuery = ref("");
const users = ref([]);
const activeTab = ref("add");

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

const sendFriendRequest = async (friendId) => {
  try {
    const response = await axiosInstance.post("/friends/request", { friend_id: friendId });
    toast.success(response.data.message || "Request sent.");
    friendshipStore.friendshipStatuses[friendId] = "pending";
  } catch (error) {
    const errorMsg = error.response?.data?.message || "An error occurred.";
    toast.error(errorMsg);
  }
};

const getButtonLabel = (userId) => {
  const status = friendshipStore.friendshipStatuses?.[userId] || "none";
  return status === "pending" ? "Pending" : status === "accepted" ? "Friends" : "Add";
};

const isActionDisabled = (userId) => {
  const status = friendshipStore.friendshipStatuses?.[userId] || "none";
  return status === "pending" || status === "accepted";
};

const canAdd = (userId) => {
  const status = friendshipStore.friendshipStatuses?.[userId] || "none";
  return status === "none";
};

const setActiveTab = (tab) => {
  activeTab.value = tab;
};

const emitClose = () => {
  emit("close");
};

onMounted(async () => {
  if (authStore.isAuthenticated) {
    await friendshipStore.fetchFriendData();
  }
});
</script>

<style scoped>
.sidebar {
  max-width: 400px;
  height: 100%;
}
.middle-header {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>
