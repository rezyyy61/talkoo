<!-- src/components/FriendRequests.vue -->

<template>
  <div class="w-full space-y-8">
    <!-- Sent Requests Section -->
    <div>
      <h3 class="w-full text-xl font-semibold mb-4 text-gray-700 dark:text-gray-200 font-sans">Sent Friend Requests</h3>
      <div v-if="sentRequests.length === 0" class="text-gray-500 dark:text-gray-400 text-center">
        You haven't sent any friend requests yet.
      </div>
      <div v-else class="space-y-2 w-full">
        <div
          v-for="request in sentRequests"
          :key="request.friend_id"
          class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg shadow-sm hover:shadow-md transition duration-200"
        >
          <!-- Avatar -->
          <div class="flex items-center gap-4">
            <div
              v-if="request.profile?.avatarImage"
              class="w-14 h-14 rounded-full overflow-hidden border border-gray-300 dark:border-gray-600"
            >
              <img
                :src="`/storage/${request.profile.avatarImage}`"
                alt="Friend Profile"
                class="w-full h-full object-cover"
              />
            </div>
            <div
              v-else
              :style="{ backgroundColor: request.profile?.avatarColor || '#ccc' }"
              class="w-14 h-14 rounded-full flex items-center justify-center text-white font-bold text-lg"
            >
              {{ request.name ? request.name.charAt(0).toUpperCase() : '?' }}
            </div>

            <!-- User Details -->
            <div class="flex flex-col">
              <span class="block text-gray-800 dark:text-gray-200 font-medium font-sans">{{ request.name }}</span>
              <span class="block text-gray-500 dark:text-gray-400 text-sm">Pending</span>
            </div>
          </div>

          <!-- Cancel Button -->
          <button
            @click="cancelSentRequest(request.friend_id)"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-red-500 dark:bg-red-600 rounded-full shadow-md hover:bg-red-600 dark:hover:bg-red-700 transition duration-200 focus:outline-none focus:ring-2 focus:ring-red-300"
            title="Cancel Friend Request"
            aria-label="Cancel Friend Request"
          >
            Cancel
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Received Requests Section -->
    <div class="w-full">
      <h3 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-200 font-sans">Received Friend Requests</h3>
      <div v-if="receivedRequests.length === 0" class="w-full text-gray-500 dark:text-gray-400 text-center">
        You don't have any incoming friend requests.
      </div>
      <div v-else class="w-full space-y-2">
        <div
          v-for="request in receivedRequests"
          :key="request.friendship_id"
          class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg shadow-sm hover:shadow-md transition duration-200"
        >
          <div class="flex items-center gap-4">
            <!-- Avatar -->
            <div
              v-if="request.profile?.avatarImage"
              class="w-14 h-14 rounded-full overflow-hidden border border-gray-300 dark:border-gray-600"
            >
              <img
                :src="`/storage/${request.profile.avatarImage}`"
                alt="Sender Profile"
                class="w-full h-full object-cover"
              />
            </div>
            <div
              v-else
              :style="{ backgroundColor: request.profile?.avatarColor || '#ccc' }"
              class="w-14 h-14 rounded-full flex items-center justify-center text-white font-bold text-lg"
            >
              {{ request.name ? request.name.charAt(0).toUpperCase() : '?' }}
            </div>

            <!-- User Details -->
            <div class="flex flex-col">
              <span class="block text-gray-800 dark:text-gray-200 font-medium font-sans">{{ request.name }}</span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-3">
            <button
              @click="acceptRequest(request.friendship_id)"
              class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-500 dark:bg-green-600 rounded-full shadow-md hover:bg-green-600 dark:hover:bg-green-700 transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-300"
              title="Accept Friend Request"
              aria-label="Accept Friend Request"
            >
              Accept
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                   viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
              </svg>
            </button>
            <button
              @click="declineRequest(request.friendship_id)"
              class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-red-500 dark:bg-red-600 rounded-full shadow-md hover:bg-red-600 dark:hover:bg-red-700 transition duration-200 focus:outline-none focus:ring-2 focus:ring-red-300"
              title="Decline Friend Request"
              aria-label="Decline Friend Request"
            >
              Decline
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                   viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useFriendshipStore } from "@/stores/friendship.js";
import { useToast } from "vue-toastification";

// Initialize the friendship store and toast
const friendshipStore = useFriendshipStore();
const toast = useToast();

// Computed properties for sent and received requests
const sentRequests = computed(() => friendshipStore.sentRequests);
const receivedRequests = computed(() => friendshipStore.receivedRequests);

// Accept a friend request
const acceptRequest = async (friendshipId) => {
  try {
    await friendshipStore.acceptFriendRequest(friendshipId);
    toast.success("Friend request accepted successfully.");
  } catch (error) {
    toast.error(error || "Failed to accept request.");
  }
};

// Decline a friend request
const declineRequest = async (friendshipId) => {
  try {
    await friendshipStore.declineFriendRequest(friendshipId);
    toast.info("Friend request declined.");
  } catch (error) {
    toast.error(error || "Failed to decline request.");
  }
};

// Cancel a sent friend request
const cancelSentRequest = async (friendId) => {
  try {
    await friendshipStore.cancelFriendRequest(friendId);
    toast.info("Friend request canceled.");
  } catch (error) {
    toast.error(error || "Failed to cancel friend request.");
  }
};

// Emit 'close' event to parent component (if applicable)
const emit = defineEmits(['close']);

const emitClose = () => {
  emit("close");
};
</script>

<style scoped>
/* Minimal and clean styles */
.bg-gray-50 {
  background-color: #f9fafb;
}

.dark:bg-gray-700 {
  background-color: #374151;
}

.shadow-sm {
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.hover\:shadow-md:hover {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
}

.rounded-lg {
  border-radius: 0.5rem;
}

.transition {
  transition: all 0.2s ease-in-out;
}

.duration-200 {
  transition-duration: 200ms;
}
</style>
