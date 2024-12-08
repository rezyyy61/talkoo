<template>
  <div class="w-full">
    <!-- Sent Requests Section -->
    <div class="mb-6">
      <h3 class="text-md font-semibold mb-2">Sent Requests</h3>
      <div v-if="sentRequests.length === 0" class="text-gray-500">No sent friend requests.</div>
      <div
        v-for="request in sentRequests"
        :key="request.friend_id"
        class="flex items-center justify-between p-4 border-b border-gray-100 hover:bg-gray-50">
        <div class="flex items-center gap-4">
          <img
            :src="request.profile_image_url || 'https://via.placeholder.com/48'"
            alt="Friend Profile"
            class="w-10 h-10 rounded-full border border-gray-200"
          />
          <div>
            <span class="text-gray-700 font-medium font-sans">{{ request.name }}</span>
            <span class="text-sm text-gray-500 font-sans">{{ request.email }}</span>
          </div>
        </div>
        <span class="text-sm text-gray-500 font-sans">Pending</span>
      </div>
    </div>

    <!-- Received Requests Section -->
    <div>
      <h3 class="text-md font-semibold mb-2">Received Requests</h3>
      <div v-if="receivedRequests.length === 0" class="text-gray-500">No received friend requests.</div>
      <div
        v-for="request in receivedRequests"
        :key="request.friendship_id"
        class="flex items-center justify-between p-4 border-b border-gray-100 hover:bg-gray-50">
        <div class="flex items-center gap-4">
          <img
            :src="request.profile_image_url || 'https://via.placeholder.com/48'"
            alt="Sender Profile"
            class="w-10 h-10 rounded-full border border-gray-200"
          />
          <div>
            <span class="text-gray-700 font-medium font-sans">{{ request.name }}</span>
            <span class="text-sm text-gray-500 font-sans">{{ request.email }}</span>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button
            @click="acceptRequest(request.friendship_id)"
            class="px-3 py-1 text-sm text-white bg-green-500 rounded hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300">
            Accept
          </button>
          <button
            @click="declineRequest(request.friendship_id)"
            class="px-3 py-1 text-sm text-white bg-red-500 rounded hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
            Decline
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useFriendshipStore } from "@/stores/friendship.js";
import { useToast } from "vue-toastification";

const friendshipStore = useFriendshipStore();
const toast = useToast();

// Using the store directly, no props needed
const sentRequests = computed(() => friendshipStore.sentRequests);
const receivedRequests = computed(() => friendshipStore.receivedRequests);

// Accept a friend request
const acceptRequest = async (friendshipId) => {
  try {
    const message = await friendshipStore.acceptFriendRequest(friendshipId);
    toast.success(message);
  } catch (error) {
    toast.error(error);
  }
};

// Decline a friend request
const declineRequest = async (friendshipId) => {
  try {
    const message = await friendshipStore.declineFriendRequest(friendshipId);
    toast.info(message);
  } catch (error) {
    toast.error(error);
  }
};
</script>

<style scoped>
/* Component-specific styles */
</style>
