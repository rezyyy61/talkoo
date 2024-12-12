<template>
  <div class="fixed top-5 right-5 flex flex-col gap-3 z-50">
    <div
      v-for="notification in notifications"
      :key="notification.id"
      class="notification-card"
      :class="`notification-${notification.type}`">
      <div class="icon-container">
        <!-- Icon based on the notification type -->
        <i class="fas fa-info-circle" v-if="notification.type === 'info'"></i>
        <i class="fas fa-check-circle" v-if="notification.type === 'success'"></i>
        <i class="fas fa-exclamation-circle" v-if="notification.type === 'warning'"></i>
        <i class="fas fa-times-circle" v-if="notification.type === 'error'"></i>
      </div>
      <div class="notification-content">
        <p class="notification-title">{{ notification.title }}</p>
        <p class="notification-message">{{ notification.message }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import { onMounted, computed, ref } from "vue";
import { useFriendshipStore } from "@/stores/friendship.js";
import { useAuthStore } from "@/stores/auth.js";

export default {
  name: "Notification",
  setup() {
    const notifications = ref([]);
    const friendshipStore = useFriendshipStore();
    const authStore = useAuthStore();
    const user = computed(() => authStore.user);

    const capitalize = (str) => {
      if (!str) return "";
      return str.charAt(0).toUpperCase() + str.slice(1);
    };

    const addNotification = (title, message, type = "info") => {
      const id = Date.now();
      notifications.value.push({ id, title, message, type });

      setTimeout(() => {
        notifications.value = notifications.value.filter((notif) => notif.id !== id);
      }, 6000);
    };

    onMounted(async () => {
      if (authStore.isAuthenticated) {
        await friendshipStore.fetchFriendData();
        if (user.value && user.value.id) {
          window.Echo.private(`newRequest.${user.value.id}`)
            .listen(".App\\Events\\FriendRequest", (event) => {
              const senderName = capitalize(event.friendship.sender_name);
              addNotification(
                `${senderName} sent you a friend request`,
                `Check your friend requests to accept or decline.`,
                "info"
              );

              friendshipStore.receivedRequests.push(event.friendship);
            });
        }
      }
    });

    onMounted(async () => {
      if (authStore.isAuthenticated) {
        await friendshipStore.fetchFriendData();
        if (user.value && user.value.id) {
          window.Echo.private(`updateRequest.${user.value.id}`)
            .listen(".FriendRequestUpdated", (event) => {
              const senderName = capitalize(event.friendship.sender_name);
              addNotification(
                `${senderName} updated their friend request`,
                event.message,
                "success"
              );

              // Update the store or handle the event as needed
              friendshipStore.receivedRequests.push(event.friendship);
            });
        }
      }
    });

    return {
      notifications,
      addNotification,
    };
  },
};
</script>

<style scoped>
/* Base Styles */
.notification-card {
  display: flex;
  align-items: center;
  padding: 1rem;
  border-radius: 0.5rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  gap: 1rem;
  animation: slideIn 0.5s ease-out forwards;
}

.notification-card:hover {
  transform: translateY(-5px);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

/* Icon Container */
.icon-container {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background-color: rgba(0, 0, 0, 0.05);
  font-size: 1.5rem;
}

.notification-content {
  flex-grow: 1;
}

.notification-title {
  font-size: 1rem;
  font-weight: bold;
  color: #333;
}

.notification-message {
  font-size: 0.875rem;
  color: #666;
}

/* Notification Types */
.notification-info {
  background-color: #e0f7fa;
  border-left: 4px solid #0288d1;
}

.notification-success {
  background-color: #e8f5e9;
  border-left: 4px solid #43a047;
}

.notification-warning {
  background-color: #fff3e0;
  border-left: 4px solid #ffa000;
}

.notification-error {
  background-color: #ffebee;
  border-left: 4px solid #d32f2f;
}

/* Animation */
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}
</style>
