// src/stores/auth.js
import { defineStore } from 'pinia';
import axiosInstance from '@/axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    authToken: localStorage.getItem('auth_token') || null,
  }),
  actions: {
    setToken(token) {
      this.authToken = token;
      localStorage.setItem('auth_token', token);
      axiosInstance.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    },
    clearToken() {
      this.authToken = null;
      localStorage.removeItem('auth_token');
      delete axiosInstance.defaults.headers.common['Authorization'];
    },
  },
  getters: {
    isAuthenticated: (state) => !!state.authToken,
  },
});

