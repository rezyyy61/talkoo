<!-- src/dashboard/RightColumn/ChatBubble.vue -->
<template>
  <div :class="bubbleClasses">
    <img
      v-if="!isSender"
      :src="message.sender.avatar || defaultAvatar"
      alt="Receiver Avatar"
      class="avatar"
    />
    <div :class="[bubbleBackground, 'bubble-content']">
      <div v-if="message.message_type === 'text'" class="text-content">
        {{ message.content }}
      </div>
      <div v-else-if="message.message_type === 'audio'" class="audio-content">
        <AudioWaveform :audioUrl="message.content" />
      </div>
      <div class="message-footer">
        <span class="timestamp">{{ formattedTime }}</span>
        <span v-if="isSender" class="status-icon">
          <svg
            v-if="message.status === 'read'"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
            class="icon read-icon"
          >
            <path d="M20.292 6.292a1 1 0 011.416 1.416l-9.003 9.003a1 1 0 01-1.415 0L7.293 13.415a1 1 0 011.415-1.415L11.29 14.58l8.998-8.289z" />
            <path d="M13.292 8.292a1 1 0 011.416 1.416L11.707 13.71a1 1 0 01-1.415 0L9.293 12.41a1 1 0 011.415-1.415L13.29 8.292z" />
          </svg>
          <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
            class="icon sent-icon"
          >
            <path d="M20.292 6.292a1 1 0 011.416 1.416l-9.003 9.003a1 1 0 01-1.415 0L7.293 13.415a1 1 0 011.415-1.415L11.29 14.58l8.998-8.289z" />
          </svg>
        </span>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';
import AudioWaveform from "@/Layouts/voices/AudioWaveform.vue";

export default {
  name: 'ChatBubble',
  components: { AudioWaveform },
  props: {
    message: {
      type: Object,
      required: true,
    },
    currentUserId: {
      type: Number,
      required: true,
    },
  },
  setup(props) {
    const defaultAvatar = 'https://readymadeui.com/team-6.webp';

    const isSender = computed(() => {
      return props.message.sender.id === props.currentUserId;
    });

    const bubbleClasses = computed(() => {
      return isSender.value
        ? 'flex items-start mb-4 flex-row-reverse'
        : 'flex items-start mb-4';
    });

    const bubbleBackground = computed(() => {
      return isSender.value ? 'bg-blue-600' : 'bg-gray-500';
    });

    const formattedTime = computed(() => {
      const date = new Date(props.message.created_at);
      return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    });

    return {
      defaultAvatar,
      isSender,
      bubbleClasses,
      bubbleBackground,
      formattedTime,
    };
  },
};
</script>

<style scoped>
.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  margin-right: 0.75rem;
  margin-left: 0.75rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  border: 2px solid #ffffff;
}

.bubble-content {
  max-width: 600px;
  padding: 0.75rem 1rem;
  border-radius: 1.25rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  position: relative;
  display: flex;
  flex-direction: column;
}

.text-content {
  font-size: 0.875rem;
  color: #ffffff;
}

.audio-content {
  width: 100%;
}

.message-footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-top: 0.5rem;
}

.timestamp {
  font-size: 0.75rem;
  color: #e2e8f0;
}

.status-icon {
  margin-left: 0.5rem;
}

.icon {
  width: 16px;
  height: 16px;
}

.read-icon {
  color: #48bb78; /* Green for read */
}

.sent-icon {
  color: #a0aec0; /* Gray for sent */
}

@media (max-width: 640px) {
  .bubble-content {
    max-width: 100%;
  }
}
</style>
