import { defineStore } from 'pinia';
import { useAuthStore } from "@/stores/auth.js";

export const useSameIp = defineStore('sameIp', {
  state: () => ({
    onlineUsers: [],
    onlineUsersCount: 0,
    isLoading: false,
    isListening: false,
    error: '',
  }),
  actions: {
    listenToOnlineUsers() {
      const authStore = useAuthStore();
      const user = authStore.user;

      if (!user || !user.profile || !user.profile.ip_address) {
        console.error('User IP address is not available.');
        return;
      }

      const ipEncoded = user.profile.ip_address.replace(/\./g, '_');
      const channelName = `sameIp.${ipEncoded}`;

      if (!window.Echo) {
        console.error('Laravel Echo is not initialized.');
        return;
      }

      if (this.isListening) {
        return;
      }

      window.Echo.private(channelName)
        .listen('UserStatusUpdated', (event) => {
          if (Array.isArray(event.users_online)) {
            this.onlineUsers = event.users_online;
            this.onlineUsersCount = event.users_online.length;
          } else {
            console.error('users_online is not an array:', event.users_online);
          }
        })
        .error((error) => {
          console.error('Subscription Error:', error);
        });

      this.isListening = true;
    }
  },
  getters: {
    formattedOnlineUsers: (state) => {
      return state.onlineUsers.map(user => ({
        ...user,
        avatar: user.avatar || 'https://readymadeui.com/team-6.webp',
        name: user.user.name,
      }));
    },
  },
});
