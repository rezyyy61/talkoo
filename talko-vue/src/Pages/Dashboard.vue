<!-- DashboardComponent.vue -->
<template>
  <div class="bg-[#434552] py-6 px-14 h-screen flex overflow-hidden">
    <!-- Left column (fixed width: 110px) -->
    <div
        class="bg-gray-600 flex flex-col items-center rounded-l-[2rem]"
        style="width: 110px; height: 100%;"
    >
      <div class="flex-1 w-full flex flex-col items-center relative">
        <div
            class="bg-[#ffffff] w-3/4 h-[100%] rounded-[2rem] flex items-center justify-center absolute"
            style="left: 0; margin-left: 0;"
        >
          <IconColumn @change-view="currentView = $event" />
        </div>
      </div>
    </div>

    <!-- Main content, Right Column, and Add User Panel -->
    <div class="flex-1 flex flex-col glass-effect relative overflow-hidden">
      <!-- Header -->
      <div class="h-[90px]">
        <Header @view-profile="toggleUserProfile" @add-user="toggleAddUserPanel" />
      </div>

      <!-- Content Area -->
      <div class="flex flex-1 transition-all duration-300 overflow-hidden">
        <!-- Middle column -->
        <div class="sidebar mx-3" :style="{ width: sidebarWidth + 'px' }">
          <div class="middle-header bg-[#8a949d] h-[100px] flex items-center justify-between border-b">
            <!-- header -->
            <h2
              class="text-xl p-3 font-extrabold text-white tracking-wide capitalize"
            >
              {{ viewTitle }}
            </h2>
          </div>
          <div class="sidebar-content bg-[#ffffff] h-full flex flex-col ">
            <!-- Conditionally render based on currentView -->
            <div v-if="currentView === 'private'"> <privateChat /></div>
            <div v-else-if="currentView === 'group'"> <GroupChat /> </div>
            <div v-else-if="currentView === 'same-ip'"> <SameIPChat /> </div>
            <div v-else-if="currentView === 'friends'">  <Friends :userId="user.id" :userName="user.name" /> </div>
            <div v-else>Default Content</div>
          </div>
          <div class="resizer" @mousedown="initResize"></div>
        </div>

        <!-- Right column -->
        <div class="bg-[#8a949d] flex-1 flex items-stretch mr-3">
          <div class="flex-1 flex items-center justify-center">
            <!--  Right Column Content-->
            <div class="userHeader"> <UserHeader /> </div>
            <div class="chatView"></div>
            <div class="chatInput"></div>

          </div>
        </div>
        <!-- User profile  Panel -->
        <transition name="slide-in">
          <div
              v-if="showUserProfile"
              class="w-80 bg-white shadow-lg flex-shrink-0 transition-transform duration-300 mx-3"
          >
            <!-- User profile Panel Content -->
            <UserProfilePanel @close-profile="toggleUserProfile" />
          </div>
        </transition>
        <!-- Add User Panel -->
        <transition name="slide-in">
          <div
              v-if="showAddUserPanel"
              class="w-[400px] bg-white shadow-lg flex-shrink-0 transition-transform duration-300 mx-3 flex flex-col overflow-hidden"
          >
            <!-- Add User Panel Content -->
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
import {defineComponent, ref, computed, onMounted} from 'vue';
import IconColumn from "@/dashboard/LeftColumn/IconColumn.vue";
import Header from "@/dashboard/MiddleColumn/Header/Header.vue";
import UserProfilePanel from "@/dashboard/MiddleColumn/Header/UserProfilePanel.vue";
import AddUserPanel from "@/dashboard/MiddleColumn/Header/AddUserPanel.vue";
import PrivateChat from "@/dashboard/MiddleColumn/PrivateChat.vue";
import GroupChat from "@/dashboard/MiddleColumn/GroupChat.vue";
import SameIPChat from "@/dashboard/MiddleColumn/SameIPChat.vue";
import Friends from "@/dashboard/MiddleColumn/Friends.vue";
import Notification from "@/Pages/Notification.vue";
import { useAuthStore } from "@/stores/auth"
import UserHeader from "@/dashboard/RightColumn/UserHeader.vue";


export default defineComponent({
  components: {
    AddUserPanel,
    UserProfilePanel,
    Header,
    IconColumn,
    PrivateChat,
    GroupChat,
    SameIPChat,
    Friends,
    Notification,
    UserHeader,
  },
  setup() {
    const sidebarWidth = ref(500);
    const isResizing = ref(false);
    const initialMouseX = ref(0);
    const initialSidebarWidth = ref(0);
    const showUserProfile = ref(false);
    const showAddUserPanel = ref(false);
    const currentView = ref('private');
    const authStore = useAuthStore();
    const user = computed(() => authStore.user);

    onMounted(() => {
      if (!authStore.user.id) {
        authStore.fetchUser();
      }
    });

    // Computed property for dynamic header title
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

    // Computed property for dynamic header icon
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

    const initResize = (event: MouseEvent) => {
      isResizing.value = true;
      initialMouseX.value = event.clientX;
      initialSidebarWidth.value = sidebarWidth.value;

      window.addEventListener("mousemove", startResizing);
      window.addEventListener("mouseup", stopResizing);
    };

    const startResizing = (event: MouseEvent) => {
      if (isResizing.value) {
        const deltaX = event.clientX - initialMouseX.value;
        sidebarWidth.value = initialSidebarWidth.value + deltaX;

        // Optional min/max
        if (sidebarWidth.value < 450) sidebarWidth.value = 450;
        if (sidebarWidth.value > 800) sidebarWidth.value = 800;
      }
    };

    const stopResizing = () => {
      isResizing.value = false;
      window.removeEventListener("mousemove", startResizing);
      window.removeEventListener("mouseup", stopResizing);
    };

    const toggleUserProfile = () => {
      if (!showUserProfile.value) {
        showUserProfile.value = true;
        showAddUserPanel.value = false;
      } else {
        showUserProfile.value = false;
      }
    };

    const toggleAddUserPanel = () => {
      if (!showAddUserPanel.value) {
        showAddUserPanel.value = true;
        showUserProfile.value = false;
      } else {
        showAddUserPanel.value = false;
      }
    };

    return {
      sidebarWidth,
      initResize,
      showUserProfile,
      toggleUserProfile,
      showAddUserPanel,
      toggleAddUserPanel,
      currentView,
      viewTitle,
      headerIcon,
      user,
    };
  },
});
</script>



<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap');

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

.resizer {
  width: 5px;
  background-color: #ccc;
  cursor: col-resize;
  position: absolute;
  top: 0;
  right: 0;
  height: 100%;
}

/* Slide-in transition for AddUserPanel */
.slide-in-enter-active,
.slide-in-leave-active {
  transition: transform 0.1s ease-in-out;
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


