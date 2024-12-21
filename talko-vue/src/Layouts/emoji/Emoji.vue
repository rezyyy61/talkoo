<!-- src/components/Emoji.vue -->
<template>
  <div class="relative group">
    <button
      @click="toggleEmojiPicker"
      class="text-gray-400 hover:text-gray-600 focus:outline-none transition-colors duration-200 p-2"
      aria-label="Insert Emoji"
    >
      <!-- Emoji SVG -->
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-6 w-6"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <circle cx="12" cy="12" r="10" />
        <path d="M15 15a3 3 0 11-6 0" />
        <path d="M9 9h.01" />
        <path d="M15 9h.01" />
      </svg>
    </button>

    <!-- Emoji Picker -->
    <div v-if="showPicker" class="absolute bottom-16 left-0 z-50">
      <emoji-picker
        class="light"
        @emoji-click="addEmoji"
        style="width: 400px; height: 300px;"
      ></emoji-picker>
    </div>
  </div>
</template>

<script>
import 'emoji-picker-element';

export default {
  name: 'Emoji',
  data() {
    return {
      showPicker: false,
    };
  },
  methods: {
    toggleEmojiPicker() {
      this.showPicker = !this.showPicker;
    },
    addEmoji(event) {
      const emoji = event.detail.unicode;
      this.$emit('emoji-selected', emoji);
      this.showPicker = false;
    },
  },
};
</script>

<style scoped>
.relative {
  position: relative;
}
.absolute {
  position: absolute;
}
.z-50 {
  z-index: 50;
}
emoji-picker {
  --num-columns: 6;
  --emoji-size: 1.5rem;
  --background: gray;
}
</style>
