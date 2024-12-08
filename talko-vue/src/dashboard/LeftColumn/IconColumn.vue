<template>
  <div class="icon-column bg-[#2e2e48] py-8 px-4 h-full overflow-auto min-w-[90px] flex flex-col items-center relative space-y-8">
    <!-- Private Chat Icon -->
    <svg
      @click="$emit('change-view', 'private')"
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 24 24"
      class="w-10 h-10 text-gray-600 cursor-pointer hover:text-gray-800 transition"
      title="Private Chat"
    >
      <rect x="6" y="10" width="12" height="10" rx="2" fill="none" stroke="currentColor" stroke-width="2" />
      <path d="M8 10V7a4 4 0 018 0v3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    </svg>

    <!-- Group Chat Icon -->
    <svg
      @click="$emit('change-view', 'group')"
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 24 24"
      class="w-10 h-10 text-gray-600 cursor-pointer hover:text-gray-800 transition"
      title="Group Chat"
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
      class="w-10 h-10 text-gray-600 cursor-pointer hover:text-gray-800 transition"
      title="Same-IP Chat"
    >
      <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2" />
      <path d="M2.05 12h19.9M12 2.05v19.9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    </svg>

    <!-- Friends Icon -->
    <svg
      @click="$emit('change-view', 'friends')"
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 24 24"
      class="w-10 h-10 text-gray-600 cursor-pointer hover:text-gray-800 transition"
      title="Friends"
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
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'IconColumn',
});
</script>

<style scoped>
</style>
