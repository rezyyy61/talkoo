<template>
  <div class="h-full flex flex-col justify-between items-center relative space-y-8">
    <!-- Icons Container -->
    <div class="flex flex-col items-center space-y-8">
      <!-- Private Chat Icon -->
      <svg
        @click="$emit('change-view', 'private')"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        class="w-10 h-10 text-gray-700 dark:text-gray-300 cursor-pointer hover:text-gray-900 dark:hover:text-white transition"
        aria-label="Private Chat"
        role="button"
        tabindex="0"
      >
        <rect x="6" y="10" width="12" height="10" rx="2" fill="none" stroke="currentColor" stroke-width="2" />
        <path d="M8 10V7a4 4 0 018 0v3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
      </svg>

      <!-- Group Chat Icon -->
      <svg
        @click="$emit('change-view', 'group')"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        class="w-10 h-10 text-gray-700 dark:text-gray-300 cursor-pointer hover:text-gray-900 dark:hover:text-white transition"
        aria-label="Group Chat"
        role="button"
        tabindex="0"
      >
        <circle cx="9" cy="8" r="4" fill="none" stroke="currentColor" stroke-width="2"/>
        <circle cx="17" cy="8" r="3" fill="none" stroke="currentColor" stroke-width="2"/>
        <path d="M2 20c0-3 4-5 9-5s9 2 9 5" fill="none" stroke="currentColor" stroke-width="2"/>
      </svg>

      <!-- Same-IP Chat Icon -->
      <svg
        @click="$emit('change-view', 'same-ip')"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        class="w-10 h-10 text-gray-700 dark:text-gray-300 cursor-pointer hover:text-gray-900 dark:hover:text-white transition"
        aria-label="Same-IP Chat"
        role="button"
        tabindex="0"
      >
        <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2" />
        <path d="M2.05 12h19.9M12 2.05v19.9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
      </svg>

      <!-- Friends Icon -->
      <svg
        @click="$emit('change-view', 'friends')"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        class="w-10 h-10 text-gray-700 dark:text-gray-300 cursor-pointer hover:text-gray-900 dark:hover:text-white transition"
        aria-label="Friends"
        role="button"
        tabindex="0"
      >
        <path
          d="M12 21c-5.052-3.96-9-7.36-9-10.4 0-2.7 2.2-4.9 4.9-4.9 1.7 0 3.3.84 4.1 2.16C12 7.84 12.6 7 14.1 7c2.7 0 4.9 2.2 4.9 4.9 0 3.04-3.948 6.44-9 10.4z"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </svg>
    </div>

    <!-- Dark/Light Mode Toggle Icon -->
    <div class="mode-toggle">
      <!-- Sun Icon for Light Mode -->
      <svg
        v-if="!isDarkMode"
        @click="$emit('toggle-theme')"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        class="w-10 h-10 text-yellow-400 dark:text-yellow-200 cursor-pointer hover:text-yellow-600 dark:hover:text-yellow-400 transition"
        aria-label="Switch to Dark Mode"
        role="button"
        tabindex="0"
      >
        <circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="2" fill="none"/>
        <line x1="12" y1="1" x2="12" y2="3" stroke="currentColor" stroke-width="2"/>
        <line x1="12" y1="21" x2="12" y2="23" stroke="currentColor" stroke-width="2"/>
        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" stroke="currentColor" stroke-width="2"/>
        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" stroke="currentColor" stroke-width="2"/>
        <line x1="1" y1="12" x2="3" y2="12" stroke="currentColor" stroke-width="2"/>
        <line x1="21" y1="12" x2="23" y2="12" stroke="currentColor" stroke-width="2"/>
        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" stroke="currentColor" stroke-width="2"/>
        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" stroke="currentColor" stroke-width="2"/>
      </svg>

      <!-- Moon Icon for Dark Mode -->
      <svg
        v-else
        @click="$emit('toggle-theme')"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        class="w-10 h-10 text-gray-300 dark:text-gray-500 cursor-pointer hover:text-gray-500 dark:hover:text-gray-300 transition"
        aria-label="Switch to Light Mode"
        role="button"
        tabindex="0"
      >
        <path
          d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        />
      </svg>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'IconColumn',
  props: {
    isDarkMode: {
      type: Boolean,
      default: false,
    },
  },
});
</script>

<style scoped>
/* No additional styles needed as Tailwind handles styling */
</style>
