// src/stores/notification.js
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useNotificationStore = defineStore('notification', () => {
  const notifications = ref([]);

  const addNotification = (title, message, type = 'info') => {
    const id = Date.now();
    notifications.value.push({ id, title, message, type });

    // Automatically remove the notification after 6 seconds
    setTimeout(() => {
      notifications.value = notifications.value.filter((notif) => notif.id !== id);
    }, 6000);
  };

  const removeNotification = (id) => {
    notifications.value = notifications.value.filter((notif) => notif.id !== id);
  };

  return {
    notifications,
    addNotification,
    removeNotification,
  };
});
