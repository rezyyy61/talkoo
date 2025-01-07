<!-- ContextMenu.vue -->
<template>
  <div
    v-if="visible"
    :style="menuStyle"
    class="dropdown-menu fixed bg-white border border-gray-300 rounded-lg shadow-md z-50"
    role="menu"
    aria-orientation="vertical"
    @click.stop
  >
    <!-- Core Actions -->
    <button
      v-for="(item, index) in coreActions"
      :key="index"
      @click="handleAction(item.action)"
      class="dropdown-item flex items-center px-4 py-3 hover:bg-gray-100 w-full text-left transition-all duration-150 ease-in-out"
    >
      <span :class="item.icon" class="mr-3 text-blue-500"></span>
      <span class="text-gray-800 font-medium">{{ item.label }}</span>
    </button>
    <hr class="border-gray-200 my-2" />

    <!-- Extra Options -->
    <div v-if="extraActions.length">
      <div class="relative dropdown inline-block">
        <button
          @click="toggleExtraMenu"
          class="dropdown-toggle dropdown-item flex items-center px-4 py-3 hover:bg-gray-100 w-full text-left transition-all duration-150 ease-in-out"
          role="menuitem"
          tabindex="-1"
        >
          <span class="icon-[tabler--text-plus] size-5 shrink-0 mr-3 text-green-500"></span>
          <span class="text-gray-800 font-medium">More</span>
          <span class="icon-[tabler--chevron-right] size-5 shrink-0 ml-auto text-gray-400"></span>
        </button>
        <div
          v-if="extraMenuVisible"
          class="dropdown-menu absolute left-full top-0 mt-0 ml-2 bg-white border border-gray-300 rounded-lg shadow-md z-50"
          role="menu"
          aria-orientation="vertical"
        >
          <button
            v-for="(item, index) in extraActions"
            :key="index"
            @click="handleAction(item.action)"
            class="dropdown-item flex items-center px-4 py-3 hover:bg-gray-100 w-full text-left transition-all duration-150 ease-in-out"
          >
            <span :class="item.icon" class="mr-3 text-green-500"></span>
            <span class="text-gray-800 font-medium">{{ item.label }}</span>
          </button>
        </div>
      </div>
    </div>
    <hr class="border-gray-200 my-2" />

    <!-- Status and Other Actions -->
    <button
      v-for="(item, index) in statusActions"
      :key="index"
      @click="handleAction(item.action)"
      class="dropdown-item flex items-center px-4 py-3 hover:bg-gray-100 w-full text-left transition-all duration-150 ease-in-out"
    >
      <span :class="item.icon" class="mr-3 text-red-500"></span>
      <span class="text-gray-800 font-medium">{{ item.label }}</span>
    </button>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, watch, onMounted, onBeforeUnmount, computed } from 'vue';

export default defineComponent({
  name: 'ContextMenu',
  props: {
    visible: {
      type: Boolean,
      default: false,
    },
    position: {
      type: Object,
      default: () => ({ x: 0, y: 0 }),
    },
  },
  emits: ['action', 'close'],
  setup(props, { emit }) {
    const extraMenuVisible = ref(false);

    const toggleExtraMenu = () => {
      extraMenuVisible.value = !extraMenuVisible.value;
    };

    const handleAction = (action: string) => {
      emit('action', action);
      emit('close');
      extraMenuVisible.value = false;
    };

    const handleClickOutside = (event: MouseEvent) => {
      emit('close');
    };

    onMounted(() => {
      if (props.visible) {
        document.addEventListener('click', handleClickOutside);
      }
    });

    watch(() => props.visible, (newVal) => {
      if (newVal) {
        document.addEventListener('click', handleClickOutside);
      } else {
        document.removeEventListener('click', handleClickOutside);
      }
    });

    onBeforeUnmount(() => {
      document.removeEventListener('click', handleClickOutside);
    });

    const coreActions = [
      { label: 'Reply', action: 'reply', icon: 'icon-[tabler--arrow-back-up] size-5' },
      { label: 'Reply All', action: 'reply_all', icon: 'icon-[tabler--arrow-back-up-double] size-5' },
      { label: 'Forward', action: 'forward', icon: 'icon-[tabler--arrow-forward-up] size-5' },
      { label: 'Resend', action: 'resend', icon: 'icon-[tabler--send] size-5' },
    ];

    const extraActions = [
      { label: 'Copy Conversation', action: 'copy_conversation', icon: 'icon-[tabler--clipboard-copy] size-5' },
      { label: 'Archive Conversation', action: 'archive_conversation', icon: 'icon-[tabler--archive] size-5' },
      { label: 'Move to Folder', action: 'move_to_folder', icon: 'icon-[tabler--mail-opened] size-5' },
      { label: 'Mark as Important', action: 'mark_as_important', icon: 'icon-[tabler--star] size-5' },
      { label: 'Mute Notifications', action: 'mute_notifications', icon: 'icon-[tabler--bell-off] size-5' },
    ];

    const statusActions = [
      { label: 'Mark as Unread', action: 'mark_as_unread', icon: 'icon-[tabler--mail] size-5' },
      { label: 'Mark as Read', action: 'mark_as_read', icon: 'icon-[tabler--mail-opened] size-5' },
      { label: 'Archive', action: 'archive', icon: 'icon-[tabler--archive] size-5' },
      { label: 'Delete', action: 'delete', icon: 'icon-[tabler--trash] size-5' },
      { label: 'Report Spam', action: 'report_spam', icon: 'icon-[tabler--alert-circle] size-5' },
      { label: 'Export Conversation', action: 'export_conversation', icon: 'icon-[tabler--file-export] size-5' },
    ];

    // Compute the menu style based on the position
    const menuStyle = computed(() => ({
      top: `${props.position.y}px`,
      left: `${props.position.x}px`,
    }));

    return {
      coreActions,
      extraActions,
      statusActions,
      extraMenuVisible,
      toggleExtraMenu,
      handleAction,
      menuStyle,
    };
  },
});
</script>

<style scoped>
.dropdown-menu {
  min-width: 260px;
  animation: fadeIn 0.2s ease-in-out;
}

.dropdown-item {
  border-radius: 0.25rem;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
