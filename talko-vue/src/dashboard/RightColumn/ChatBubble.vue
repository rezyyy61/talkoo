<!-- src/dashboard/RightColumn/ChatBubble.vue -->
<template>
  <div :class="bubbleClasses">
    <!-- Conditional rendering for the avatar -->
    <template v-if="!isSender">
      <img
        v-if="message.sender.profile.avatarImage"
        :src="`/storage/${message.sender.profile.avatarImage}`"
        alt="Receiver Avatar"
        class="w-10 h-10 rounded-full shadow-md border-2 border-white mx-3"
      />
      <div
        v-else
        :style="{ backgroundColor: message.sender.profile.avatarColor || '#fffff' }"
        class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white mx-3 shadow-md"
      >
        {{ getInitials(message.sender.name) }}
      </div>
    </template>

    <div :class="[bubbleBackground, 'relative max-w-xl p-3 rounded-2xl shadow-md flex flex-col']">
      <div v-if="message.message_type === 'text'" class="text-sm text-white">
        {{ message.content }}
      </div>
      <div v-else-if="message.message_type === 'audio'" class="w-full">
        <AudioWaveform :audioUrl="message.content" />
      </div>

      <div
        v-else-if="['file', 'image', 'video', 'pdf'].includes(message.message_type)"
        class="file-content"
      >
        <ChatFileDisplay
          v-for="file in message.files"
          :key="file.id"
          :file="file"
        />
      </div>

      <div class="flex justify-end items-center mt-2 text-xs text-gray-200">
        <span>{{ formattedTime }}</span>
        <span v-if="isSender" class="ml-2">
          <svg
            v-if="message.status === 'read'"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
            class="w-4 h-4 text-green-500"
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
            class="w-4 h-4 text-gray-400"
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
import AudioWaveform from "@/Layouts/voices/AudioWaveform.vue";
import ChatFileDisplay from "@/Layouts/file/ChatFileDisplay.vue";

export default {
  name: 'ChatBubble',
  components: { AudioWaveform, ChatFileDisplay },
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
    const isSender = computed(() => {
      return props.message.sender.id === props.currentUserId;
    });

    console.log(props.message.sender);

    const bubbleClasses = computed(() => {
      return isSender.value
        ? 'flex items-start mb-4 flex-row-reverse'
        : 'flex items-start mb-4';
    });

    const bubbleBackground = computed(() => {
      return isSender.value ? 'bg-blue-500' : 'bg-gray-500';
    });

    const formattedTime = computed(() => {
      const date = new Date(props.message.created_at);
      return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    });

    const getInitials = (name) => {
      if (!name) return '?';
      const names = name.trim().split(' ');
      return names.length > 1
        ? names[0].charAt(0).toUpperCase() + names[1].charAt(0).toUpperCase()
        : names[0].charAt(0).toUpperCase();
    };

    return {
      isSender,
      bubbleClasses,
      bubbleBackground,
      formattedTime,
      getInitials,
    };
  },
};

</script>


