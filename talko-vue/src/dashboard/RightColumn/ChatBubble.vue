<!-- src/dashboard/RightColumn/ChatBubble.vue -->
<template>
  <div :class="bubbleContainerClasses">
    <!-- Avatar for Receiver -->
    <template v-if="!isSender">
      <div class="avatar mr-2">
        <img
          v-if="message.sender.profile.avatarImage"
          :src="`/storage/${message.sender.profile.avatarImage}`"
          alt="Receiver Avatar"
          class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-lg"
        />
        <div
          v-else
          :style="{ backgroundColor: message.sender.profile.avatarColor || '#ccc' }"
          class="w-12 h-12 rounded-full flex items-center justify-center text-white text-lg font-bold border-2 border-white shadow-lg"
        >
          {{ getInitials(message.sender.name) }}
        </div>
      </div>
    </template>

    <!-- Message Bubble -->
    <div
      class="relative inline-block p-4 rounded-2xl shadow-md"
      @contextmenu.prevent="onRightClick"
      :class="['relative max-w-lg p-4 rounded-2xl shadow-md flex flex-col', bubbleBackground, messageAlignment]">
      <!-- Message Content -->
      <div v-if="message.message_type === 'text'" class="text-sm text-gray-900 dark:text-gray-100 break-words">
        {{ message.content }}
      </div>
      <div v-else-if="message.message_type === 'audio'" class="w-full">
        <AudioWaveform :audioUrl="message.content" />
      </div>
      <div
        v-else-if="['file', 'image', 'video', 'pdf'].includes(message.message_type)"
        class="file-content mt-2"
      >
        <ChatFileDisplay
          v-for="file in message.files"
          :key="file.id"
          :file="file"
        />
      </div>

      <!-- Timestamp and Status -->
      <div class="flex justify-end items-center mt-3 text-xs text-gray-500 dark:text-gray-400">
        <span>{{ formattedTime }}</span>
        <span v-if="isSender" class="ml-2 flex items-center">
          <svg
            v-if="message.status === 'read'"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
            class="w-4 h-4 text-green-500"
            aria-label="Message Read"
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
            aria-label="Message Sent"
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

<script lang="ts">
import { defineComponent, computed } from 'vue';
import AudioWaveform from "@/Layouts/voices/AudioWaveform.vue";
import ChatFileDisplay from "@/Layouts/file/ChatFileDisplay.vue";

export default defineComponent({
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
  emits: ['right-clicked'],

  setup(props, { emit }) {
    // Determine if the current user is the sender
    const isSender = computed(() => {
      return props.message.sender.id === props.currentUserId;
    });

    // Container classes based on sender/receiver
    const bubbleContainerClasses = computed(() => {
      return isSender.value
        ? 'flex justify-end'
        : 'flex justify-start';
    });

    // Bubble background color based on sender/receiver and theme
    const bubbleBackground = computed(() => {
      return isSender.value
        ? 'bg-blue-500 dark:bg-blue-600 text-white'
        : 'bg-gray-300 dark:bg-gray-700 text-gray-900 dark:text-gray-100';
    });

    // Align message bubble
    const messageAlignment = computed(() => {
      return isSender.value ? 'items-end' : 'items-start';
    });

    // Format timestamp to a readable format
    const formattedTime = computed(() => {
      const date = new Date(props.message.created_at);
      return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    });

    // Get initials from a user's name
    const getInitials = (name: string): string => {
      if (!name) return '?';
      const names = name.trim().split(' ');
      if (names.length === 1) {
        return names[0].charAt(0).toUpperCase();
      }
      return (names[0].charAt(0) + names[1].charAt(0)).toUpperCase();
    };

    // Handle user click event
    const handleUserClick = (user: any) => {
      if (user.id === props.currentUserId) {
        emit('open-profile', props.message.sender);
        return;
      }

      const transformedUser = {
        id: user.user?.id || user.id,
        name: user.user?.name || user.name,
        email: user.user?.email || '',
        email_verified_at: null,
        created_at: user.created_at || '',
        updated_at: user.updated_at || '',
        profile: {
          id: user.id,
          user_id: user.user?.id || user.id,
          userId: user.userId || '',
          ip_address: user.ip_address || '',
          is_online: user.is_online || 0,
          last_activity: user.last_activity || '',
          avatarImage: user.avatarImage || '',
          avatarColor: user.avatarColor || '',
          bio: user.bio || null,
          created_at: user.created_at || '',
          updated_at: user.updated_at || '',
        },
      };

      emit('open-profile', transformedUser);
    };

    const onRightClick = (event: MouseEvent) => {
      event.preventDefault();
      emit('right-clicked', { message: props.message, event });
    };


    return {
      isSender,
      bubbleContainerClasses,
      bubbleBackground,
      messageAlignment,
      formattedTime,
      getInitials,
      handleUserClick,
      onRightClick
    };
  },
});
</script>
