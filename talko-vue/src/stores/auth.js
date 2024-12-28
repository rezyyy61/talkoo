// src/stores/auth.js
import { defineStore } from 'pinia';
import axiosInstance from '@/axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    authToken: localStorage.getItem('auth_token') || null,
    user: JSON.parse(localStorage.getItem('user')) || {
      id: null,
      name: '',
      email: '',
      avatar: '',
      profile: {
        id: null,
        user_id: null,
        ip_address: '',
        is_online: false,
        last_activity: '',
        avatar: '',
      },
      social: {},
    },
  }),
  actions: {
    setToken(token) {
      this.authToken = token;
      localStorage.setItem('auth_token', token);
      axiosInstance.defaults.headers.common['Authorization'] = `Bearer ${token}`;

      if (window.Echo) {
        window.Echo.options.auth.headers['Authorization'] = `Bearer ${token}`;
      }
    },
    setUser(user) {
      this.user = {
        ...user,
        profile: {
          id: user.profile?.id ?? null,
          user_id: user.profile?.user_id ?? null,
          ip_address: user.profile?.ip_address ?? '',
          is_online: user.profile?.is_online ?? false,
          last_activity: user.profile?.last_activity ?? '',
          avatar: user.profile?.avatar ?? '',
        },
        social: user.social || {},
      };
      localStorage.setItem('user', JSON.stringify(this.user));
    },

    async fetchUser() {
      if (!this.authToken) return;
      try {
        const response = await axiosInstance.get('/user');
        this.setUser(response.data);
      } catch (error) {
        console.error('Failed to fetch user:', error);
        await this.logOut();
      }
    },

    async logOut() {
      try {
        await axiosInstance.post('/logout');
        this.authToken = null;
        this.user = {
          id: null,
          name: '',
          email: '',
          avatar: '',
          profile: {
            id: null,
            user_id: null,
            ip_address: null,
            is_online: false,
            last_activity: null,
            avatar: null,
          },
          social: {},
          projects: 0,
          tasks: 0,
          phone: '',
          address: '',
        };
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        delete axiosInstance.defaults.headers.common['Authorization'];
      } catch (error) {
        console.error('Logout error:', error.response ? error.response.data : error.message);
      }
    },
  },
  getters: {
    isAuthenticated: (state) => !!state.authToken,
  },
});
