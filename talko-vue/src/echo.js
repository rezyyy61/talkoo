import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
// import {useAuthStore} from "@/stores/auth.js";
window.Pusher = Pusher;

window.Echo = new Echo({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_REVERB_APP_KEY,
  wsHost: import.meta.env.VITE_REVERB_HOST,
  wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
  wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
  forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
  enabledTransports: ['ws', 'wss'],
  logToConsole: true,
  authEndpoint: 'http://127.0.0.1:8080/broadcasting/auth',
  auth: {
    headers: {
      Authorization: `Bearer ${localStorage.getItem('auth_token')}`,

    },
    withCredentials: true,
  },
});

// const userStore = useAuthStore();


// window.Echo.join(`userOnline.${userStore.user.id}`)
//   .here((users) => {
//     users.forEach(user => {
//       userStore.updateUserStatus(user.id, user.is_online);
//     });
//   })
//   .joining((user) => {
//     userStore.updateUserStatus(user.id, true);
//   })
//   .leaving((user) => {
//     userStore.updateUserStatus(user.id, false);
//   })
//   .listen('UserStatusUpdated', (e) => {
//     userStore.updateUserStatus(e.user_id, e.is_online);
//   });
