// src/stores/auth.js
import { defineStore } from 'pinia';
import axiosInstance from '@/axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    authToken: localStorage.getItem('auth_token') || null,
    user: JSON.parse(localStorage.getItem('user')) || {
      name: '',
      email: '',
      avatar: '',
      isOnline: false,
      lastActive: null,
      social: {}, // Initialize as an empty object
      projects: 0,
      tasks: 0,
      phone: '',
      address: '',
      // Add other default properties as needed
    },
  }),
  actions: {
    setToken(token) {
      this.authToken = token;
      localStorage.setItem('auth_token', token);
      axiosInstance.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    },
    setUser(user) {
      this.user = {
        ...user,
        social: user.social || {}, // Ensure social is always an object
      };
      localStorage.setItem('user', JSON.stringify(this.user));
    },
    clearAuth() {
      this.authToken = null;
      this.user = {
        name: '',
        email: '',
        avatar: '',
        isOnline: false,
        lastActive: null,
        social: {},
        projects: 0,
        tasks: 0,
        phone: '',
        address: '',
      };
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      delete axiosInstance.defaults.headers.common['Authorization'];
    },
    async fetchUser() {
      if (!this.authToken) return;
      try {
        const response = await axiosInstance.get('/user'); // Adjust endpoint as needed
        this.setUser(response.data.user);
      } catch (error) {
        console.error('Failed to fetch user:', error);
        this.clearAuth();
      }
    },
  },
  getters: {
    isAuthenticated: (state) => !!state.authToken,
  },
});
