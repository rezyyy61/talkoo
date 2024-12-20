// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router';
import Welcome from "@/Pages/Welcome.vue";
import Register from "@/Pages/Auth/Register.vue";
import Login from "@/Pages/Auth/Login.vue";
import Dashboard from "@/Pages/Dashboard.vue";
import Test from "@/dashboard/RightColumn/Test.vue";

const routes = [
  {
    path: '/',
    name: 'Welcome',
    component: Welcome,
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    meta: { requiresAuth: false },
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { requiresAuth: false },
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true },
  },
  {
    path: '/test',
    name: 'Test',
    component: Test,
    meta: { requiresAuth: true },
  },
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
});

// Navigation Guard for Authenticated Routes
router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('auth_token');

  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/login'); // Redirect to Login if not authenticated
  } else if ((to.name === 'Login' || to.name === 'Register') && isAuthenticated) {
    next('/dashboard'); // Redirect authenticated users away from login/register
  } else {
    next();
  }
});

export default router;
