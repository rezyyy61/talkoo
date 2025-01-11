<template>
  <div class="p-4">
    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">
      {{ viewTitle }}
    </h2>
    <!-- Conditionally Render Based on currentView -->
    <div v-if="currentView === 'private'">
      <Friends
        @select-user="handleSelectUser"
        :userId="user.id"
        :userName="user.name"
        :selectedUser="selectedUser"
      />
    </div>
    <div v-else-if="currentView === 'group'">
      <GroupChat />
    </div>
    <div v-else-if="currentView === 'same-ip'">
      <SameIpChat :userProp="user" @open-profile="toggleUserProfile" />
    </div>
    <div v-else>
      <p class="text-gray-600 dark:text-gray-400">Default Content</p>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import Friends from "@/dashboard/MiddleColumn/Friends.vue";
import GroupChat from "@/dashboard/MiddleColumn/GroupChat.vue";
import SameIpChat from "@/dashboard/MiddleColumn/SameIPChat.vue";

export default defineComponent({
  name: 'MiddleColumn',
  components: { Friends, GroupChat, SameIpChat },
  props: {
    currentView: {
      type: String,
      required: true,
    },
    viewTitle: {
      type: String,
      required: true,
    },
    user: {
      type: Object,
      required: true,
    },
    selectedUser: {
      type: Object,
      default: null,
    },
  },
  emits: ['select-user', 'toggle-user-profile'],
  methods: {
    handleSelectUser(user) {
      this.$emit('select-user', user);
    },
    toggleUserProfile(user) {
      this.$emit('toggle-user-profile', user);
    },
  },
});
</script>
