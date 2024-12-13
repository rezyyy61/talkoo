<template>
  <div class="chatView flex-1 p-4 overflow-y-auto w-full h-full flex items-center justify-center">
    <div class="text-center text-white text-xl font-bold">
      <p>No messages yet. Start the conversation!</p>
    </div>
  </div>
</template>

<script>
import ChatBubble from "@/dashboard/RightColumn/ChatBubble.vue";

export default {
  name: 'ChatView',
  components: {
    // ChatBubble,
  },
};
</script>

<style scoped>
/* Optional: Customize scrollbar for better aesthetics */
.chatView::-webkit-scrollbar {
  width: 8px;
}

.chatView::-webkit-scrollbar-track {
  background: #1f2937; /* Tailwind's gray-800 */
}

.chatView::-webkit-scrollbar-thumb {
  background-color: #4b5563; /* Tailwind's gray-600 */
  border-radius: 4px;
}
</style>
