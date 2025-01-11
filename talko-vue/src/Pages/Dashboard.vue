<template>
  <div :class="{ 'dark': isDarkMode }" class="bg-[#434552] py-6 px-14 h-screen flex overflow-hidden transition-colors duration-300">
    <!-- Left Column (IconColumn) -->
    <div class="left-column shared-column-style border border-blue-200 bg-white dark:bg-gray-800 shadow-lg rounded-lg transition-colors duration-300">
      <IconColumn
        @change-view="currentView = $event"
        @toggle-theme="toggleTheme"
        :isDarkMode="isDarkMode"
      />
    </div>

    <!-- Main Content Area -->
    <div class="main-content flex-1 flex flex-col glass-effect relative overflow-hidden bg-gray-100 dark:bg-gray-800 transition-colors duration-300">
      <!-- Header -->
      <div class="header h-[90px] bg-white dark:bg-gray-800 shadow-md transition-colors duration-300">
        <Header @view-profile="toggleUserProfile" @add-user="toggleAddUserPanel" />
      </div>

      <!-- Content Area with Middle, Right Columns, and Panels -->
      <div class="content-area flex flex-1 gap-4 p-4 flex-col md:flex-row">
        <!-- Middle Column -->
        <MiddleColumn
          class="w-full md:w-1/4 bg-gray-50 dark:bg-gray-700 shadow-md rounded-lg transition-colors duration-300 hover:shadow-lg"
          :currentView="currentView"
          :viewTitle="viewTitle"
          :user="user"
          :selectedUser="selectedUser"
          @select-user="handleSelectUser"
          @toggle-user-profile="toggleUserProfile"
        />

        <!-- Vertical Divider -->
        <div class="hidden md:block border-l border-gray-300 dark:border-gray-700"></div>

        <!-- Right Column -->
        <RightColumn
          class="flex-1 bg-gray-200 dark:bg-gray-600 shadow-md rounded-lg transition-colors duration-300 hover:shadow-lg"
          :currentView="currentView"
          :selectedUser="selectedUser"
          :user="user"
          :groupHeader="groupHeader"
          @toggle-user-profile="toggleUserProfile"
        />

        <!-- Transition Group for Panels -->
        <transition-group
          name="slide-group"
          tag="div"
          :class="['flex-shrink-0 flex flex-col gap-4 transition-all duration-300', panelWidth]"
        >
          <div
            v-if="showUserProfile"
            key="user-profile"
            class="profile-panel h-screen bg-gray-50 dark:bg-gray-700 shadow-md rounded-lg hover:shadow-lg"
          >
            <UserProfilePanel :user="profileUser" @close="toggleUserProfile" />
          </div>
          <div
            v-if="showAddUserPanel"
            key="add-user"
            class="add-user-panel bg-gray-50 dark:bg-gray-700 shadow-md rounded-lg hover:shadow-lg"
          >
            <AddUserPanel @close="toggleAddUserPanel" />
          </div>
        </transition-group>
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
import MiddleColumn from "@/dashboard/MiddleColumn/MiddleColumn.vue";
import RightColumn from "@/dashboard/RightColumn/RightColumn.vue";
import Notification from "@/Pages/Notification.vue";
import { useAuthStore } from "@/stores/auth";
import Header from "@/dashboard/MiddleColumn/Header/Header.vue";
import { useMessageStore } from '@/stores/message';

export default defineComponent({
  name: 'DashboardComponent',
  components: {
    IconColumn,
    UserProfilePanel,
    AddUserPanel,
    MiddleColumn,
    RightColumn,
    Notification,
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

    // Theme State
    const isDarkMode = ref(false);

    const toggleTheme = () => {
      isDarkMode.value = !isDarkMode.value;
      // Persist the theme preference
      localStorage.setItem('theme', isDarkMode.value ? 'dark' : 'light');

      // Apply the theme by toggling the 'dark' class on the root element
      if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    };

    // Initialize theme based on user preference or system settings
    const initializeTheme = () => {
      const savedTheme = localStorage.getItem('theme');
      if (savedTheme === 'dark') {
        isDarkMode.value = true;
      } else if (savedTheme === 'light') {
        isDarkMode.value = false;
      } else {
        // Default to system preference
        isDarkMode.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
      }

      if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
      }
    };

    initializeTheme();

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

    // Computed property for dynamic panel width
    const panelWidth = computed(() => {
      if (showUserProfile.value || showAddUserPanel.value) {
        return 'w-2/6';
      }
      return 'w-0';
    });

    return {
      showUserProfile,
      toggleUserProfile,
      showAddUserPanel,
      toggleAddUserPanel,
      currentView,
      viewTitle,
      groupHeader,
      user,
      selectedUser,
      profileUser,
      handleSelectUser,
      isDarkMode,
      toggleTheme,
      panelWidth,
    };
  },
});
</script>

<style scoped>
.left-column {
  padding: 1.5rem;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  transition: background-color 0.3s;
  border-radius: 2rem 0 0 2rem;
}

.main-content {
  display: flex;
  flex-direction: column;
  height: 100%;
  backdrop-filter: blur(10px);
  border-radius: 0 1rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.5);
}

.header {
  background-color: #ffffff;
  dark:bg-gray-800;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: background-color 0.3s;
}

.content-area {
  display: flex;
  flex: 1;
  overflow: hidden;
}

.slide-group-enter-active,
.slide-group-leave-active {
  transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
}

.slide-group-enter-from,
.slide-group-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

.slide-group-enter-to,
.slide-group-leave-from {
  transform: translateX(0);
  opacity: 1;
}

/* Additional Styles to Enhance Transition-Group Size */
.profile-panel,
.add-user-panel {
  /* Ensure the panels take full available height */
  max-height: 100vh;
  /* Optional: Add padding or margin if needed */
  padding: 1rem;
}

/* Apply transition to width */
.slide-group {
  transition: width 0.3s ease-in-out;
}

/* Ensure the transition-group can handle dynamic width */
.transition-all {
  transition: all 0.3s ease-in-out;
}
</style>
