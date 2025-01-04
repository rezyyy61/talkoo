// stores/auth.js

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
        console.error('Error fetching user data', error);
        this.clearUser();
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
      this.user = userData;
      localStorage.setItem('user', JSON.stringify(userData));
    },

    clearUser() {
      this.user = null;
      this.authToken = null;
      localStorage.removeItem('user');
      localStorage.removeItem('auth_token');
      delete axiosInstance.defaults.headers.common['Authorization'];
      if (window.Echo) {
        window.Echo.options.auth.headers['Authorization'] = '';
      }
    },

    async logOut() {
      try {
        await axiosInstance.post('/logout');
        this.clearUser();
        // Redirect to login after logout
        window.location.href = '/login'; // Ensure page refresh/redirection
      } catch (error) {
        console.error('Logout error:', error.response ? error.response.data : error.message);
      }
    },

    async updateProfile(profileData) {
      try {
        const formData = new FormData();

        formData.append('name', profileData.name || '');
        formData.append('userId', profileData.userId || '');
        formData.append('bio', profileData.bio || '');

        if (profileData.avatarFile) {
          formData.append('avatarImage', profileData.avatarFile);
        }

        if (profileData.avatarColor) {
          formData.append('avatarColor', profileData.avatarColor);
        }

        // The key: Only append removeAvatar if it's true
        if (profileData.removeAvatar) {
          formData.append('removeAvatar', 'true');
        }

        const response = await axiosInstance.post('/user/profile/update', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        });

        this.setUser(response.data.user);
        return response.data;
      } catch (error) {
        console.error('Error updating profile:', error);
        throw error;
      }
    },
  },

  getters: {
    isAuthenticated: (state) => !!state.authToken,
    getUserProfile: (state) => state.user?.profile,
  },
});
