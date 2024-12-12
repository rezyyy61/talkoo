<template>
  <header class="bg-white shadow-md px-6 py-4 flex items-center justify-between">
    <!-- Left Section: Logo or App Name -->
    <div class="flex items-center">
      <img src="" alt="App Logo" class="h-10 w-10 mr-3" />
      <span class="text-xl font-semibold text-gray-800">ChatApp</span>
    </div>

    <!-- Right Section: Notifications and User Profile -->
    <div class="flex items-center space-x-4">
      <!-- Notifications -->
      <div class="relative">
        <button @click="toggleNotifications" class="text-gray-600 hover:text-gray-800 focus:outline-none">
          <svg
            class="h-6 w-6"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
            ></path>
          </svg>
          <!-- Notification Badge -->
          <span
            v-if="notifications.length > 0"
            class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full"
          >
            {{ notifications.length }}
          </span>
        </button>
        <!-- Notifications Dropdown -->
        <transition name="fade">
          <div v-if="showNotifications" class="absolute right-0 mt-2 w-64 bg-white border rounded-md shadow-lg z-20">
            <div class="p-4">
              <h3 class="text-lg font-semibold text-gray-700">Notifications</h3>
              <ul class="mt-2">
                <li v-for="(notification, index) in notifications" :key="index" class="flex items-center py-2 border-b last:border-b-0">
                  <svg class="h-5 w-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 10a8 8 0 1116 0 8 8 0 01-16 0zm8-6a6 6 0 00-5.916 5H4a8 8 0 0115.832 0h-0.084A6 6 0 0010 4z" />
                  </svg>
                  <span class="text-gray-600">{{ notification }}</span>
                </li>
              </ul>
              <button @click="clearNotifications" class="mt-3 w-full text-center text-blue-500 hover:underline">
                Mark all as read
              </button>
            </div>
          </div>
        </transition>
      </div>

      <!-- User Profile -->
      <div class="relative">
        <button @click="toggleUserMenu" class="flex items-center focus:outline-none">
          <img :src="user.avatar" alt="User Avatar" class="h-10 w-10 rounded-full object-cover" />
          <span class="ml-2 text-gray-800 font-medium">{{ user.name }}</span>
          <svg
            class="ml-1 h-4 w-4 text-gray-600"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
              clip-rule="evenodd"
            />
          </svg>
        </button>
        <!-- User Dropdown Menu -->
        <transition name="fade">
          <div v-if="showUserMenu" class="absolute right-0 mt-2 w-48 bg-white border rounded-md shadow-lg z-20">
            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Settings</a>
            <a href="#" @click.prevent="logout" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
          </div>
        </transition>
      </div>
    </div>
  </header>
</template>

<script>
export default {
  name: "UserHeader",
  data() {
    return {
      showUserMenu: false,
      showNotifications: false,
      notifications: [
        "New message from Alice",
        "Your profile was viewed",
        "Update available",
      ],
      user: {
        name: "John Doe",
        avatar: "https://via.placeholder.com/150",
      },
    };
  },
  methods: {
    toggleUserMenu() {
      this.showUserMenu = !this.showUserMenu;
      if (this.showNotifications) this.showNotifications = false;
    },
    toggleNotifications() {
      this.showNotifications = !this.showNotifications;
      if (this.showUserMenu) this.showUserMenu = false;
    },
    clearNotifications() {
      this.notifications = [];
      this.showNotifications = false;
    },
    logout() {
      // Handle logout logic here
      this.$emit("logout");
    },
  },
  mounted() {
    // Close dropdowns when clicking outside
    document.addEventListener("click", this.handleClickOutside);
  },
  beforeUnmount() {
    document.removeEventListener("click", this.handleClickOutside);
  },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
