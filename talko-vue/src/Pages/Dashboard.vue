<!-- src/Pages/Dashboard.vue -->
<template>
  <!-- header -->
  <div></div>

  <div class="container">
    <!-- Left column for icons -->
    <div class="bg-[#2e2e48] py-8 h-full overflow-auto flex flex-col items-center">
    </div>

    <!-- Middle column for dynamic content -->
    <div class="sidebar" :style="{ width: sidebarWidth + 'px' }">
      <div class="sidebar-content bg-[#38385f] h-full overflow-auto flex flex-col items-center rounded-tr-2xl rounded-br-2xl">

      </div>
      <div class="resizer" @mousedown="initResize"></div>
    </div>

    <!-- Right column for the chat UI -->
    <div class="main">
      Main Content
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue';


export default defineComponent({

  setup() {

    // Resizing logic remains the same
    const sidebarWidth = ref(500);
    const isResizing = ref(false);
    const initialMouseX = ref(0);
    const initialSidebarWidth = ref(0);

    const initResize = (event: MouseEvent) => {
      isResizing.value = true;
      initialMouseX.value = event.clientX;
      initialSidebarWidth.value = sidebarWidth.value;

      window.addEventListener('mousemove', startResizing);
      window.addEventListener('mouseup', stopResizing);
    };

    const startResizing = (event: MouseEvent) => {
      if (isResizing.value) {
        const deltaX = event.clientX - initialMouseX.value;
        sidebarWidth.value = initialSidebarWidth.value + deltaX;

        // Optional: Set minimum and maximum sidebar widths
        if (sidebarWidth.value < 450) sidebarWidth.value = 450;
        if (sidebarWidth.value > 800) sidebarWidth.value = 800;
      }
    };

    const stopResizing = () => {
      isResizing.value = false;
      window.removeEventListener('mousemove', startResizing);
      window.removeEventListener('mouseup', stopResizing);
    };

    return {
      sidebarWidth,
      initResize,
    };
  },
});
</script>

<style scoped>
.container {
  display: flex;
  height: 100vh;
}

.sidebar {
  background-color: #f4f4f4;
  border-right: 1px solid #ccc;
  position: relative;
}

.sidebar-content {
  padding: 15px;
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

.main {
  flex: 1;
  padding: 15px;
}
</style>
