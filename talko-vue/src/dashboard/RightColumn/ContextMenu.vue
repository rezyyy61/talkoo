<template>
  <Teleport to="body">
    <div
      v-if="visible"
      ref="menuContainer"
      :class="containerClasses"
      :style="{ top: `${position.y}px`, left: `${position.x}px` }"
      @click.stop
    >
      <ul class="py-1">
        <li
          v-for="(item, index) in menuItems"
          :key="index"
          @click="handleClick(item)"
          :class="itemClasses"
        >
          <span>{{ item.label }}</span>
        </li>
      </ul>
    </div>
  </Teleport>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted, onBeforeUnmount, nextTick, computed, watch } from 'vue';

export default defineComponent({
  name: 'ContextMenu',
  props: {
    visible: {
      type: Boolean,
      required: true,
    },
    position: {
      type: Object,
      required: true,
      default: () => ({ x: 0, y: 0 }),
    },
    menuItems: {
      type: Array,
      required: true,
      default: () => [],
    },
  },
  emits: ['close', 'select'],
  setup(props, { emit }) {
    const menuContainer = ref<HTMLElement | null>(null);

    const handleClickOutside = (event: MouseEvent) => {
      if (!menuContainer.value) return;
      if (!menuContainer.value.contains(event.target as Node)) {
        emit('close');
      }
    };
    watch(
      () => props.visible,
      (newVal) => {
        if (newVal) {
          nextTick(() => {
            adjustPositionIfNeeded();
          });
        }
      }
    );


    const adjustPositionIfNeeded = () => {
      nextTick(() => {

        if (!menuContainer.value) {
          return;
        }

        const { offsetWidth, offsetHeight } = menuContainer.value;
        const { innerWidth, innerHeight } = window;

        if (!props.position || typeof props.position !== 'object') {
          console.log('props.position is undefined or not an object');
          return;
        }

        let { x, y } = { ...props.position };

        if (x + offsetWidth > innerWidth) {
          x = x - offsetWidth;
          if (x < 10) x = 10;
        }

        if (y + offsetHeight > innerHeight) {
          y = y - offsetHeight;
          if (y < 10) y = 10;
        }

        // Apply final positions
        menuContainer.value.style.left = `${x}px`;
        menuContainer.value.style.top = `${y}px`;

      });
    };

    const handleClick = (item: any) => {
      emit('select', item);
      emit('close');
    };

    onMounted(() => {
      document.addEventListener('click', handleClickOutside);
      adjustPositionIfNeeded();
    });

    onBeforeUnmount(() => {
      document.removeEventListener('click', handleClickOutside);
    });

    // Example container classes
    const containerClasses = computed(() => [
      'fixed z-50 border rounded shadow-lg w-48',
      'bg-white border-gray-200',
      'dark:bg-gray-800 dark:border-gray-700',
    ]);

    // Example item classes
    const itemClasses = computed(() => [
      'px-4 py-2 flex items-center space-x-2 cursor-pointer',
      'hover:bg-gray-100 dark:hover:bg-gray-700',
      'text-gray-800 dark:text-gray-200',
    ]);

    return {
      menuContainer,
      containerClasses,
      itemClasses,
      handleClick,
      adjustPositionIfNeeded,
    };
  },
});
</script>
