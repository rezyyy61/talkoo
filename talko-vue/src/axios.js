// src/axios.js
import axios from 'axios';
import router from './router';

const axiosInstance = axios.create({
  baseURL: 'http://127.0.0.1:8080/api',
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add a request interceptor to include the auth token if it exists
axiosInstance.interceptors.request.use(
    (config) => {
      const token = localStorage.getItem('auth_token');
        if (token) {
        config.headers['Authorization'] = `Bearer ${token}`;
      }
      return config;
    },
    (error) => {
      return Promise.reject(error);
    }
);

// Add a response interceptor to handle unauthorized responses
axiosInstance.interceptors.response.use(
    (response) => response,
    (error) => {
      if (error.response && error.response.status === 401) {
        // Clear auth token
        localStorage.removeItem('auth_token');

        // Redirect to login
        router.push('/login');
      }
      return Promise.reject(error);
    }
);

export default axiosInstance;
