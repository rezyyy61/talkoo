<template>
  <div :class="bubbleClasses">
    <img
      v-if="!isSender"
      :src="message.sender.avatar || defaultAvatar"
      alt="Receiver Avatar"
      class="w-10 h-10 rounded-full mr-3 shadow-md border-2 border-white"
    />
    <div :class="[bubbleBackground, 'max-w-md text-white p-4  rounded-xl shadow-lg']">
      <div class="text-base">
        {{ message.content }}
      </div>
      <div class="text-xs text-gray-200 mt-2">
        {{ formattedTime }}
        <span v-if="isSender" class="ml-2">
          <svg
            v-if="message.status === 'read'"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
            class="w-5 h-5 text-white inline-block"
          >
            <path
              d="M20.292 6.292a1 1 0 011.416 1.416l-9.003 9.003a1 1 0 01-1.415 0L7.293 13.415a1 1 0 011.415-1.415L11.29 14.58l8.998-8.289z"
            />
            <path
              d="M13.292 8.292a1 1 0 011.416 1.416L11.707 13.71a1 1 0 01-1.415 0L9.293 12.41a1 1 0 011.415-1.415L13.29 8.292z"
            />
          </svg>
          <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
            class="w-5 h-5 text-white inline-block"
          >
            <path
              d="M20.292 6.292a1 1 0 011.416 1.416l-9.003 9.003a1 1 0 01-1.415 0L7.293 13.415a1 1 0 011.415-1.415L11.29 14.58l8.998-8.289z"
            />
          </svg>
        </span>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'ChatBubble',
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
      return isSender.value ? 'bg-[#3652AD]' : 'bg-gray-500';
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
.flex {
  display: flex;
}

.items-start {
  align-items: flex-start;
}

.mb-4 {
  margin-bottom: 1rem;
}

.flex-row-reverse {
  flex-direction: row-reverse;
}

.rounded-full {
  border-radius: 9999px;
}

.text-sm {
  font-size: 0.875rem;
}

.text-base {
  font-size: 1rem;
}

.text-xs {
  font-size: 0.75rem;
}

.max-w-md {
  max-width: 24rem;
}

.bg-blue-600 {
  background-color: #2563eb;
}

.bg-gray-500 {
  background-color: #6b7280;
}

.text-white {
  color: #ffffff;
}

.p-4 {
  padding: 1rem;
}

.rounded-xl {
  border-radius: 0.75rem;
}

.shadow-lg {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 2px 4px rgba(0, 0, 0, 0.06);
}

.shadow-md {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.border-2 {
  border-width: 2px;
}

.border-white {
  border-color: #ffffff;
}

.text-green-400 {
  color: #10b981;
}

.text-gray-400 {
  color: #9ca3af;
}
</style>
