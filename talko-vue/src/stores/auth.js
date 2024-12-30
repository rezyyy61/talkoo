import { defineStore } from 'pinia';
import axiosInstance from '@/axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    authToken: localStorage.getItem('auth_token') || null,
    user: JSON.parse(localStorage.getItem('user')) || null,
  }),

  actions: {
    async fetchUser() {
      if (!this.authToken) return;
      try {
        const response = await axiosInstance.get('/user');
        this.setUser(response.data.user);
      } catch (error) {
        console.error("Error fetching user data", error);
        this.clearUser(); // Clear user data if there's an error
      }
    },

    setToken(token) {
      this.authToken = token;
      localStorage.setItem('auth_token', token);
      axiosInstance.defaults.headers.common['Authorization'] = `Bearer ${token}`;

      if (window.Echo) {
        window.Echo.options.auth.headers['Authorization'] = `Bearer ${token}`;
      }
    },

    setUser(userData) {
      this.user = userData; // Store the user data in the Pinia state
      localStorage.setItem('user', JSON.stringify(userData)); // Save user data to localStorage
    },

    clearUser() {
      this.user = null;
      this.authToken = null;
      localStorage.removeItem('user');
      localStorage.removeItem('auth_token');
    },

    async logOut() {
      try {
        await axiosInstance.post('/logout');
        this.clearUser();  // Clear user and token data
        delete axiosInstance.defaults.headers.common['Authorization'];
        if (window.Echo) {
          window.Echo.options.auth.headers['Authorization'] = ''; // Clear WebSocket auth headers
        }
      } catch (error) {
        console.error('Logout error:', error.response ? error.response.data : error.message);
      }
    },
  },

  getters: {
    isAuthenticated: (state) => !!state.authToken,
    getUserProfile: (state) => state.user?.profile,
  },

  // Automatically fetch user data if authenticated
  onBeforeMount() {
    if (this.authToken) {
      this.fetchUser();
    }
  }
});
