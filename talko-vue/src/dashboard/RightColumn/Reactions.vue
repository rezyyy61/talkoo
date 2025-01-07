<template>
  <div class="reactions flex items-center space-x-2">
    <span
      v-for="emoji in emojis"
      :key="emoji"
      class="emoji"
      tabindex="0"
      role="button"
      :aria-label="getAriaLabel(emoji)"
      @click.stop="emitReaction(emoji)"
      @keydown.enter.prevent.stop="emitReaction(emoji)"
    >
      {{ emoji }}
    </span>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'Reactions',
  props: {
    emojis: {
      type: Array,
      default: () => ['😊', '😂', '👍', '❤️', '🎉']
    }
  },
  emits: ['react'],
  methods: {
    emitReaction(emoji: string) {
      this.$emit('react', emoji);
    },
    getAriaLabel(emoji: string) {
      switch (emoji) {
        case '😊':
          return 'Happy';
        case '😂':
          return 'Laughing';
        case '👍':
          return 'Thumbs Up';
        case '❤️':
          return 'Love';
        case '🎉':
          return 'Celebration';
        default:
          return 'React';
      }
    }
  }
});
</script>

<style scoped>
.emoji {
  font-size: 1.25rem;
  cursor: pointer;
  transition: transform 0.2s, color 0.2s;
}

.emoji:hover {
  transform: scale(1.2);
  color: #2563eb;
}
</style>
