<!-- src/dashboard/RightColumn/UserProfilePanel.vue -->
<template>
  <div class="bg-white shadow-lg rounded-3xl p-6 w-full h-full overflow-auto flex flex-col">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800">My Profile</h2>
      <button @click="closeProfile" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
    </div>

    <!-- Profile Information -->
    <div v-if="user" class="flex flex-col items-center md:flex-row md:items-start mb-6">
      <img
        :src="user.avatar || defaultAvatar"
        alt="User Avatar"
        class="w-32 h-32 rounded-full object-cover shadow-md"
      />
      <div class="mt-4 md:mt-0 md:ml-6 text-center md:text-left">
        <h3 class="text-xl font-semibold text-gray-700">{{ user.name || 'Anonymous' }}</h3>
        <p class="text-gray-500">{{ user.email || 'No Email Provided' }}</p>
        <p class="text-gray-500">
          Status: <span :class="statusClass">{{ userStatus }}</span>
        </p>
        <p class="text-gray-500">Last Active: {{ formattedLastActive }}</p>
      </div>
    </div>

    <!-- Quick Actions -->
    <div v-if="user" class="flex justify-around mb-6">
      <button
        @click="startNewChat"
        class="flex items-center px-4 py-2 bg-blue-500 text-white rounded-full hover:bg-blue-600 transition"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        New Chat
      </button>
      <button
        @click="editProfile"
        class="flex items-center px-4 py-2 bg-green-500 text-white rounded-full hover:bg-green-600 transition"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-1.4a4 4 0 00-6.2 0H7a3 3 0 00-3 3v2h5" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z" />
        </svg>
        Edit Profile
      </button>
    </div>

    <!-- Contact Information -->
    <div v-if="user" class="mb-6">
      <h3 class="text-xl font-semibold text-gray-800 mb-4">Contact Information</h3>
      <ul class="space-y-2">
        <li class="flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12H8m8 0V8m0 4v4m0-4h.01" />
          </svg>
          <span>{{ user.email || 'N/A' }}</span>
        </li>
        <li class="flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-18 0l9 12 9-12" />
          </svg>
          <span>{{ user.phone || 'N/A' }}</span>
        </li>
        <li class="flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12h-.01M12 16h-.01M8 12h-.01M12 8h-.01" />
          </svg>
          <span>{{ user.address || 'N/A' }}</span>
        </li>
      </ul>
    </div>

    <!-- Social Links (Optional) -->
    <div v-if="user && hasSocialLinks" class="mb-6">
      <h3 class="text-xl font-semibold text-gray-800 mb-4">Social Links</h3>
      <ul class="flex space-x-4">
        <li>
          <a :href="user.social.twitter" target="_blank" rel="noopener noreferrer" class="text-blue-500 hover:text-blue-700" v-if="user.social?.twitter">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
              <path d="M24 4.557a9.9 9.9 0 01-2.828.775 4.932 4.932 0 002.165-2.724c-.951.564-2.005.974-3.127 1.195A4.916 4.916 0 0016.616 3c-2.73 0-4.932 2.201-4.932 4.932 0 .386.044.762.128 1.124C7.691 8.094 4.066 6.13 1.64 3.161a4.822 4.822 0 00-.666 2.475c0 1.708.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.061c0 2.385 1.693 4.374 3.946 4.827a4.902 4.902 0 01-2.224.084c.63 1.953 2.445 3.377 4.6 3.417A9.867 9.867 0 010 19.54a13.94 13.94 0 007.548 2.212c9.057 0 14.01-7.513 14.01-14.01 0-.213-.005-.425-.014-.636A10.012 10.012 0 0024 4.557z"/>
            </svg>
          </a>
        </li>
        <li>
          <a :href="user.social.linkedin" target="_blank" rel="noopener noreferrer" class="text-blue-700 hover:text-blue-900" v-if="user.social?.linkedin">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
              <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-10h3v10zm-1.5-11.268c-.966 0-1.75-.785-1.75-1.75s.784-1.75 1.75-1.75 1.75.785 1.75 1.75-.784 1.75-1.75 1.75zm13.5 11.268h-3v-5.604c0-1.337-.024-3.065-1.872-3.065-1.873 0-2.158 1.461-2.158 2.964v5.7h-3v-10h2.881v1.367h.041c.401-.761 1.379-1.562 2.838-1.562 3.037 0 3.6 2.0 3.6 4.6v5.572z"/>
            </svg>
          </a>
        </li>
        <li>
          <a :href="user.social.github" target="_blank" rel="noopener noreferrer" class="text-gray-800 hover:text-black" v-if="user.social?.github">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 0C5.373 0 0 5.373 0 12c0 5.303 3.438 9.8 8.205 11.387.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 17.07 3.633 16.7 3.633 16.7c-1.087-.744.083-.729.083-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.305-5.466-1.332-5.466-5.931 0-1.31.469-2.381 1.235-3.221-.124-.303-.535-1.523.117-3.176 0 0 1.008-.322 3.3 1.23a11.5 11.5 0 013.003-.404c1.018.005 2.042.138 3.003.404 2.29-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.873.118 3.176.77.84 1.233 1.911 1.233 3.221 0 4.61-2.805 5.624-5.475 5.921.43.371.823 1.102.823 2.222 0 1.606-.015 2.898-.015 3.293 0 .321.216.694.825.576C20.565 21.796 24 17.299 24 12c0-6.627-5.373-12-12-12z"/>
            </svg>
          </a>
        </li>
        <!-- Add more social links as needed -->
      </ul>
    </div>

    <!-- Recent Chats -->
    <div v-if="user" class="flex-1">
      <h3 class="text-xl font-semibold text-gray-800 mb-4">Recent Chats</h3>
      <ul class="space-y-4">
        <li v-for="chat in recentChats" :key="chat.id" class="flex items-center p-4 bg-gray-100 rounded-xl hover:bg-gray-200 transition cursor-pointer">
          <img
            :src="chat.avatar || defaultAvatar"
            alt="Chat Avatar"
            class="w-12 h-12 rounded-full object-cover"
          />
          <div class="ml-4 flex-1">
            <h4 class="text-lg font-medium text-gray-700">{{ chat.name }}</h4>
            <p class="text-sm text-gray-500">{{ chat.lastMessage }}</p>
          </div>
          <span class="text-xs text-gray-400">{{ chat.time }}</span>
        </li>
        <!-- Add more chats as needed -->
      </ul>
    </div>

    <!-- Logout Button -->
    <div v-if="user" class="mt-6 flex justify-end">
      <button
        @click="logout"
        class="flex items-center px-6 py-3 bg-red-500 text-white font-semibold rounded-full hover:bg-red-600 transition"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
        Logout
      </button>
    </div>

    <!-- Fallback Content if User is Not Available -->
    <div v-else class="flex-1 flex items-center justify-center">
      <p class="text-gray-500">Loading profile...</p>
    </div>
  </div>
</template>

<script setup>
import { defineEmits, computed } from 'vue';
import { useAuthStore } from '@/stores/auth.js';
import { useRouter } from 'vue-router';

// Define the emits for this component
const emit = defineEmits(['close-profile']);

// Path to a default avatar image. Replace with your desired default image URL if necessary.
const defaultAvatar = 'https://via.placeholder.com/150';

// Access the auth store and router
const authStore = useAuthStore();
const router = useRouter();

// Computed property to access user data
const user = computed(() => authStore.user);

// Computed property for user status
const userStatus = computed(() => {
  return user.value.isOnline ? 'Online' : `Last active: ${formatLastActive(user.value.lastActive)}`;
});

// Computed property for status class
const statusClass = computed(() => {
  return user.value.isOnline ? 'text-green-500' : 'text-gray-500';
});

// Computed property for formatted last active time
const formattedLastActive = computed(() => {
  return formatLastActive(user.value.lastActive);
});

// Function to format the last active time
function formatLastActive(date) {
  if (!date) return 'N/A';
  const d = new Date(date);
  return d.toLocaleString(undefined, { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

// Mock data for recent chats (Replace with actual data from your backend)
const recentChats = [
  {
    id: 1,
    name: 'John Doe',
    avatar: 'https://via.placeholder.com/150',
    lastMessage: 'Hey, how are you?',
    time: '2h ago',
  },
  {
    id: 2,
    name: 'Jane Smith',
    avatar: 'https://via.placeholder.com/150',
    lastMessage: 'Let\'s catch up tomorrow!',
    time: '5h ago',
  },
  // Add more chats as needed
];

// Logout function
const logout = () => {
  authStore.clearAuth();
  router.push('/login');
};

// Close profile function
const closeProfile = () => {
  emit('close-profile');
};

// Start new chat function (Implement as needed)
const startNewChat = () => {
  // Logic to start a new chat
  console.log('Starting a new chat...');
};

// Edit profile function (Implement as needed)
const editProfile = () => {
  // Logic to edit profile, e.g., navigate to edit profile page
  console.log('Editing profile...');
};

// Computed property to check if there are any social links
const hasSocialLinks = computed(() => {
  return user.value.social && Object.keys(user.value.social).length > 0;
});
</script>

<style scoped>
/* Custom scrollbar for better aesthetics */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background-color: rgba(0, 0, 0, 0.1);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background-color: rgba(0, 0, 0, 0.2);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .flex-col {
    flex-direction: column;
  }

  .md\:flex-row {
    flex-direction: column;
  }
}
</style>
