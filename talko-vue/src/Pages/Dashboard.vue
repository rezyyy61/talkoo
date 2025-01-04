<template>
  <div class="bg-[#434552] py-6 px-14 h-screen flex overflow-hidden">
    <!-- Left Column (Fixed Width: 110px) -->
    <div
      class="bg-gray-600 flex flex-col items-center rounded-l-[2rem]"
      style="width: 110px; height: 100%;"
    >
      <div class="flex-1 w-full flex flex-col items-center relative">
        <div
          class="bg-white w-3/4 h-full rounded-[2rem] flex items-center justify-center absolute left-0"
        >
          <IconColumn @change-view="currentView = $event" />
        </div>
      </div>
    </div>

    <!-- Main Content, Right Column, and Add User Panel -->
    <div class="flex-1 flex flex-col glass-effect relative overflow-hidden">
      <!-- Header -->
      <div class="h-[90px]">
        <Header @view-profile="toggleUserProfile" @add-user="toggleAddUserPanel" />
      </div>

      <!-- Content Area -->
      <div class="flex flex-1 transition-all duration-300 overflow-hidden ">
        <!-- Middle Column -->
        <div class="w-[450px] sidebar mx-3 flex flex-col">
          <div class="bg-[#8a949d] p-3 h-[120px] title flex items-center">
            <span class="circle-indicator"></span>
            <h2 class="text-2xl font-extrabold text-white tracking-wide capitalize ml-2">
              {{ viewTitle }}
            </h2>
          </div>

          <div class="sidebar-content bg-[#ffffff] dark:bg-gray-900 h-full flex flex-col overflow-y-auto">
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
              Default Content
            </div>
          </div>
        </div>

        <!-- Right Column -->
        <div class="bg-[#8a949d] flex-1 flex flex-col mr-2">
          <!-- ChatHeader Section -->
          <div v-if="currentView === 'same-ip'" class="userHeader mb-4">
            <ChatHeader :user="groupHeader" />
          </div>
          <div
            v-else-if="selectedUser && selectedUser.id !== user.id"
            class="userHeader mb-4"
          >
            <ChatHeader :user="selectedUser" @open-profile="toggleUserProfile" />
          </div>
          <div v-else class="userHeader mb-4">
            <p class="text-white">Select a user to view details.</p>
          </div>

          <!-- Chat View Section -->
          <div v-if="currentView === 'same-ip' || selectedUser" class="flex-1 overflow-y-auto">
            <ChatView />
          </div>

          <div v-else class="flex-1 flex items-center justify-center">
            <p class="text-gray-500 dark:text-gray-400">Chat view goes here...</p>
          </div>

          <!-- Input Section -->
          <div class="chatInput mt-4 mb-2">
            <ChatInput :chatType="currentView === 'same-ip' ? 'group' : 'private'" />
          </div>
        </div>

        <!-- User Profile Panel -->
        <transition name="slide-in">
          <div
            v-if="showUserProfile"
            class="w-[450px] bg-white shadow-lg flex-shrink-0 transition-transform duration-300 rounded-xl overflow-y-auto max-h-screen"
          >
            <UserProfilePanel
              v-if="showUserProfile"
              :user="profileUser"
              @close="toggleUserProfile" />

          </div>
        </transition>

        <!-- Add User Panel -->
        <transition name="slide-in">
          <div
            v-if="showAddUserPanel"
            class="w-[450px] bg-white shadow-lg flex-shrink-0 transition-transform duration-300 mx-3 flex flex-col overflow-hidden"
          >
            <AddUserPanel @close="toggleAddUserPanel" />
          </div>
        </transition>
      </div>
    </div>

    <!-- Notifications -->
    <notification />
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, computed, watch } from 'vue';
import IconColumn from "@/dashboard/LeftColumn/IconColumn.vue";
import UserProfilePanel from "@/dashboard/MiddleColumn/Header/UserProfilePanel.vue";
import AddUserPanel from "@/dashboard/MiddleColumn/Header/AddUserPanel.vue";
import PrivateChat from "@/dashboard/MiddleColumn/PrivateChat.vue";
import GroupChat from "@/dashboard/MiddleColumn/GroupChat.vue";
import Friends from "@/dashboard/MiddleColumn/Friends.vue";
import Notification from "@/Pages/Notification.vue";
import { useAuthStore } from "@/stores/auth";
import ChatHeader from "@/dashboard/RightColumn/ChatHeader.vue";
import ChatInput from "@/dashboard/RightColumn/ChatInput.vue";
import ChatView from "@/dashboard/RightColumn/ChatView.vue";
import Header from "@/dashboard/MiddleColumn/Header/Header.vue";
import { useMessageStore } from '@/stores/message';
import SameIpChat from "@/dashboard/MiddleColumn/SameIPChat.vue";

export default defineComponent({
  name: 'DashboardComponent',
  components: {
    SameIpChat,
    IconColumn,
    UserProfilePanel,
    AddUserPanel,
    PrivateChat,
    GroupChat,
    Friends,
    Notification,
    ChatHeader,
    ChatInput,
    ChatView,
    Header,
  },
  setup() {
    const showUserProfile = ref(false);
    const showAddUserPanel = ref(false);
    const currentView = ref('private');
    const authStore = useAuthStore();
    const user = computed(() => authStore.user);
    const selectedUser = ref(null);
    const profileUser = ref(null);

    const viewTitle = computed(() => {
      switch (currentView.value) {
        case 'private':
          return 'Private Chat';
        case 'group':
          return 'Group Chat';
        case 'same-ip':
          return 'Same-IP Chat';
        case 'friends':
          return 'Friends';
        default:
          return 'Dashboard';
      }
    });

    const headerIcon = computed(() => {
      switch (currentView.value) {
        case 'private':
          return 'PrivateIcon';
        case 'group':
          return 'GroupIcon';
        case 'same-ip':
          return 'SameIPIcon';
        case 'friends':
          return 'FriendsIcon';
        default:
          return 'DefaultIcon';
      }
    });

    const groupHeader = computed(() => {
      if (currentView.value === 'same-ip') {
        return {
          id: 0,
          name: 'Group',
          profile: {
            avatar: '#d1d5db',
            is_online: false,
          },
        };
      }
      return selectedUser.value;
    });

    const toggleUserProfile = (user: any = null) => {
      if (user) {
        if (profileUser.value && profileUser.value.id === user.id && showUserProfile.value) {
          showUserProfile.value = false;
          profileUser.value = null;
          return;
        }
        profileUser.value = user;
        showUserProfile.value = true;
        showAddUserPanel.value = false;
      } else {
        showUserProfile.value = false;
        profileUser.value = null;
      }
    };

    const toggleAddUserPanel = () => {
      showAddUserPanel.value = !showAddUserPanel.value;
      if (showAddUserPanel.value) {
        showUserProfile.value = false;
      }
    };

    const handleSelectUser = (user: any) => {
      if (user.id !== authStore.user.id) {
        selectedUser.value = user;
        const messageStore = useMessageStore();
        messageStore.setReceiverId(user.id);
        messageStore.getPrivateConversationId(user.id)
          .then(() => {
            messageStore.fetchMessages();
          });
      }
    };


    watch(
      () => currentView.value,
      async (newVal) => {
        if (newVal === 'same-ip') {
          const messageStore = useMessageStore();
          try {
            await messageStore.getSameIPConversation();
            await messageStore.fetchMessages();
          } catch (error) {
            console.error(error);
          }
        }
      }
    );

    return {
      showUserProfile,
      toggleUserProfile,
      showAddUserPanel,
      toggleAddUserPanel,
      currentView,
      viewTitle,
      groupHeader,
      headerIcon,
      user,
      selectedUser,
      profileUser,
      handleSelectUser,
    };
  },
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Anton&family=DM+Serif+Text:ital@1&family=PT+Serif+Caption:ital@0;1&display=swap');

.middle-header {
  font-family: 'Montserrat', sans-serif;
}

.container {
  display: flex;
  height: 100%;
  width: 100%;
  overflow: hidden;
  border-radius: 50px;
}

.sidebar {
  position: relative;
  overflow: hidden;
  z-index: 1;
}

.glass-effect {
  background: #d5d6da;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.5);
}

.sidebar-content {
  width: 100%;
  height: 100%;
  box-sizing: border-box;
  background: #ffffff;
}

.title {
  border-top-left-radius: 20px;
  border-top-right-radius: 20px;
}

.title h2 {
  font-family: "DM Serif Text", serif;
  font-weight: 400;
  font-style: italic;
}

.circle-indicator {
  width: 36px;
  height: 36px;
  background-color: #71b3bb;
  border-radius: 50%;
}

/* Slide-in transition for AddUserPanel and UserProfilePanel */
.slide-in-enter-active,
.slide-in-leave-active {
  transition: transform 0.3s ease-in-out;
}
.slide-in-enter-from {
  transform: translateX(100%);
}
.slide-in-enter-to {
  transform: translateX(0);
}
.slide-in-leave-from {
  transform: translateX(0);
}
.slide-in-leave-to {
  transform: translateX(100%);
}
</style>
