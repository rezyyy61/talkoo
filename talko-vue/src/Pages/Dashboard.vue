<template>
  <div class="bg-[#cdd0d5]  py-6 h-screen flex justify-center items-center">
    <!-- Left column (fixed width: 110px, with adjusted nested div) -->
    <div class="bg-gray-600 flex flex-col items-center rounded-l-[2rem]" style="width: 110px; height: 100%;">
      <div class="flex-1 w-full flex flex-col items-center relative">
        <div
          class="bg-[#ffffff] w-3/4 h-[100%] rounded-[2rem] flex items-center justify-center absolute"
          style="left: 0; margin-left: 0;"
        >
        </div>
      </div>
    </div>

    <!-- Middle and right columns (with shared header) -->
    <div class="container flex-1 flex flex-col  glass-effect">
      <!-- Header -->
      <div class="h-[70px] ">
        <UserProfile @view-profile="toggleUserProfile" />
      </div>
      <!-- Middle column -->
      <div class=" flex flex-1 rounded-[2rem]">
        <div class="sidebar mx-6" :style="{ width: sidebarWidth + 'px' }">
          <div class="middle-header bg-[#b8e5e2] h-[80px] flex items-center justify-between border-b">
            <h2 class="text-lg font-semibold">Middle Column Header</h2>
          </div>
          <div class="sidebar-content bg-[#fffff] h-full flex flex-col items-center rounded-tr-2xl rounded-br-2xl">
            Middle Column Content
          </div>
          <div class="resizer" @mousedown="initResize"></div>
        </div>

        <!-- Right column -->
        <div class="bg-[#afc7cb] flex-1 flex items-stretch">
          <div class="flex-1 flex items-center justify-center p-4">
            <UserProfileDetail v-if="showUserProfile" @close-profile="showUserProfile = false" />
            <div v-else class="text-gray-800">
              Right Column Content
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue';
import IconColumn from "@/dashboard/LeftColumn/IconColumn.vue";
import UserProfile from "@/dashboard/MiddleColumn/UserProfile.vue";
import UserProfileDetail from "@/dashboard/RightColumn/UserProfileDetail.vue";

export default defineComponent({
  components: {
    UserProfileDetail,
    UserProfile,
    IconColumn,
  },
  setup() {
    const sidebarWidth = ref(500);
    const isResizing = ref(false);
    const initialMouseX = ref(0);
    const initialSidebarWidth = ref(0);
    const showUserProfile = ref(false);

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
      showUserProfile.value = !showUserProfile.value;
    };

    return {
      sidebarWidth,
      initResize,
      showUserProfile,
      toggleUserProfile
    };
  },
});
</script>

<style scoped>
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
</style>

