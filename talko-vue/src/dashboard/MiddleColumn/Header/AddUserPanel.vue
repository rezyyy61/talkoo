<template>
  <div class="sidebar w-[400px] bg-white shadow-lg rounded-xl flex flex-col"
       style="height: calc(100vh - 100px);">
    <!-- ChatHeader -->
    <div class="middle-header bg-[#8a949d] h-[80px] flex items-center justify-between px-6 text-white rounded-t-xl">
      <h2 class="text-lg font-semibold font-sans">Friends</h2>
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
          title="You have new friend requests"
        >
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
            class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-[#8a949d] font-sans"
          />
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
              <img
                :src="userItem.profile_image_url || 'https://via.placeholder.com/48'"
                alt="User Profile"
                class="w-12 h-12 rounded-full border border-gray-200"
              />
              <div class="flex flex-col">
                <span class="text-gray-700 font-medium font-sans">{{ userItem.name }}</span>
                <span class="text-sm text-gray-500 font-sans">{{ userItem.email }}</span>
              </div>
            </div>
            <button
              :disabled="isActionDisabled(userItem.id)"
              @click="sendFriendRequest(userItem.id)"
              class="px-4 py-2 text-sm text-white bg-[#8a949d] rounded-full hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 font-sans flex items-center justify-center">
              {{ getButtonLabel(userItem.id) }}
              <svg v-if="canAdd(userItem.id)" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- View Requests Tab -->
      <div v-if="activeTab === 'requests'">
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
    users.value = response.data || [];

    // Fetch their friendship statuses if needed
    const userIds = users.value.map(user => user.id);
    if (userIds.length > 0) {
      await friendshipStore.getFriendshipStatuses(userIds);
    }
  } catch (error) {
    console.error("Error searching users:", error);
    toast.error("Failed to search users.");
  }
}, 300);

// Send friend request
const sendFriendRequest = async (friendId) => {
  try {
    const response = await axiosInstance.post("/friends/request", {
      friend_id: friendId
    });

    // Success response
    toast.success(response.data.message || "Request sent.");
    // Update status to pending immediately
    friendshipStore.friendshipStatuses[friendId] = "pending";
  } catch (error) {
    if (error.response && error.response.status === 400) {
      const errorMsg = error.response.data.message;
      if (errorMsg === "Friend request already pending.") {
        // Set status to pending if already pending
        friendshipStore.friendshipStatuses[friendId] = "pending";
        toast.info("Request is already pending.");
      } else if (errorMsg === "You are already friends.") {
        // Set status to accepted if already friends
        friendshipStore.friendshipStatuses[friendId] = "accepted";
        toast.info("You are already friends.");
      } else {
        toast.error(errorMsg);
      }
    } else {
      toast.error("An error occurred while sending the friend request.");
    }
  }
};

// Returns the button label based on friendship status
const getButtonLabel = (userId) => {
  const status = friendshipStore.friendshipStatuses?.[userId] || 'none';
  if (status === "pending") return "Pending";
  if (status === "accepted") return "Friends";
  return "Add";
};

// Determine if the action button should be disabled
const isActionDisabled = (userId) => {
  const status = friendshipStore.friendshipStatuses?.[userId] || 'none';
  return status === "pending" || status === "accepted";
};

// Determine if the add icon should be displayed
const canAdd = (userId) => {
  const status = friendshipStore.friendshipStatuses?.[userId] || 'none';
  // Show icon only if status is none
  return status === "none";
};

// Change active tab
const setActiveTab = (tab) => {
  activeTab.value = tab;
};

// Emit close event to close the panel
const emitClose = () => {
  emit('close');
};

// On mount, fetch initial data if authenticated
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
