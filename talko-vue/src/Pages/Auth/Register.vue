<template>
  <div class="h-screen bg-gradient-to-br from-[#8e2de2] via-[#4a00e0] to-[#2e2d51] text-white flex justify-center items-center overflow-hidden">
    <div class="w-full max-w-screen-2xl lg:flex shadow-2xl rounded-3xl transform transition-all duration-500 hover:shadow-3xl">
      <!-- Left Panel -->
      <div
        class="hidden lg:flex lg:w-1/2 bg-cover bg-center bg-no-repeat rounded-l-3xl"
        style="background-image: url('/register-image.jpg');"
      >
      <div class="flex items-center justify-center w-full h-full bg-gradient-to-t from-[#4a00e0] via-transparent to-[#4a00e0]">
        <h2 class="text-5xl font-extrabold text-white drop-shadow-2xl animate-fadeIn">Join the Community!</h2>
      </div>
    </div>

    <!-- Right Panel -->
    <div class="w-full lg:w-1/2 bg-white text-gray-800 p-10 lg:p-16 flex items-center justify-center rounded-r-3xl h-full">
      <div class="w-full">
        <div class="flex justify-center mb-8">
          <img src="#" class="w-24 shadow-2xl rounded-full border-4 border-[#8e2de2] transition-transform duration-500 ease-in-out transform hover:scale-125" alt="Logo" />
        </div>
        <h1 class="text-4xl font-extrabold text-center mb-6 text-[#4a00e0] animate-slideIn">Sign Up for Free</h1>

        <!-- Social Sign Up Buttons -->
        <div class="space-y-4">
          <button
            @click="signUpWithGoogle"
            type="button"
            class="w-full font-semibold shadow-md rounded-full py-4 bg-[#8e2de2] text-white flex items-center justify-center transition-all duration-300 ease-in-out focus:outline-none hover:bg-[#6d18cc] hover:shadow-xl hover:-translate-y-1.5"
          >
            <div class="bg-white p-2 rounded-full">
              <!-- Google SVG -->
              <svg class="w-5" viewBox="0 0 533.5 544.3">
                <path d="M533.5 278.4c0-18.5-1.5-37.1-4.7-55.3H272.1v104.8h147c-6.1 33.8-25.7 63.7-54.4 82.7v68h87.7c51.5-47.4 81.1-117.4 81.1-200.2z" fill="#4285f4" />
                <path d="M272.1 544.3c73.4 0 135.3-24.1 180.4-65.7l-87.7-68c-24.4 16.6-55.9 26-92.6 26-71 0-131.2-47.9-152.8-112.3H28.9v70.1c46.2 91.9 140.3 149.9 243.2 149.9z" fill="#34a853" />
                <path d="M119.3 324.3c-11.4-33.8-11.4-70.4 0-104.2V150H28.9c-38.6 76.9-38.6 167.5 0 244.4l90.4-70.1z" fill="#fbbc04" />
                <path d="M272.1 107.7c38.8-.6 76.3 14 104.4 40.8l77.7-77.7C405 24.6 339.7-.8 272.1 0 169.2 0 75.1 58 28.9 150l90.4 70.1c21.5-64.5 81.8-112.4 152.8-112.4z" fill="#ea4335" />
              </svg>
            </div>
            <span class="ml-4">Sign Up with Google</span>
          </button>

          <button
            @click="signUpWithGitHub"
            type="button"
            class="w-full font-semibold shadow-md rounded-full py-4 bg-gray-800 text-white flex items-center justify-center transition-all duration-300 ease-in-out focus:outline-none hover:bg-gray-900 hover:shadow-xl hover:-translate-y-1.5"
          >
            <div class="bg-white p-2 rounded-full">
              <!-- GitHub SVG -->
              <svg class="w-6" viewBox="0 0 32 32">
                <path fill-rule="evenodd" d="M16 4C9.371 4 4 9.371 4 16c0 5.3 3.438 9.8 8.207 11.387.602.11.82-.258.82-.578 0-.286-.011-1.04-.015-2.04-3.34.723-4.043-1.609-4.043-1.609-.547-1.387-1.332-1.758-1.332-1.758-1.09-.742.082-.726.082-.726 1.203.086 1.836 1.234 1.836 1.234 1.07 1.836 2.808 1.305 3.492 1 .11-.777.422-1.305.762-1.605-2.664-.301-5.465-1.332-5.465-5.93 0-1.313.469-2.383 1.234-3.223-.121-.3-.535-1.523.117-3.175 0 0 1.008-.32 3.301 1.23A11.487 11.487 0 0116 9.805c1.02.004 2.047.136 3.004.402 2.293-1.55 3.297-1.23 3.297-1.23.656 1.652.246 2.875.12 3.175.77.84 1.231 1.91 1.231 3.223 0 4.61-2.804 5.621-5.476 5.922.43.367.812 1.101.812 2.219 0 1.605-.011 2.898-.011 3.293 0 .32.214.695.824.578C24.566 25.797 28 21.3 28 16c0-6.629-5.371-12-12-12z" />
              </svg>
            </div>
            <span class="ml-4">Sign Up with GitHub</span>
          </button>
        </div>

        <div class="my-10 border-b text-center relative">
          <div class="leading-none px-4 inline-block text-sm text-gray-500 tracking-wide font-medium bg-white absolute left-1/2 transform -translate-x-1/2 -translate-y-1/2">
            Or sign up with email
          </div>
        </div>

        <!-- Email Sign Up Form -->
        <form @submit.prevent="submit">
          <div>
            <InputLabel for="name" value="Name" />

            <TextInput
              id="name"
              type="text"
              class="p-3 mt-1 block w-full bg-gray-100 border-gray-300 rounded-full hover:border-[#8e2de2] focus:border-[#8e2de2] focus:ring-[#8e2de2] focus:ring-opacity-50 transition-all"
              v-model="form.name"
              required
              autofocus
              autocomplete="name"
            />

            <InputError class="mt-2" :message="form.errors.name" />
          </div>

          <div class="mt-4">
            <InputLabel for="email" value="Email" />

            <TextInput
              id="email"
              type="email"
              class="p-3 mt-1 block w-full bg-gray-100 border-gray-300 rounded-full hover:border-[#8e2de2] focus:border-[#8e2de2] focus:ring-[#8e2de2] focus:ring-opacity-50 transition-all"
              v-model="form.email"
              required
              autocomplete="username"
            />

            <InputError class="mt-2" :message="form.errors.email" />
          </div>

          <div class="mt-4">
            <InputLabel for="password" value="Password" />

            <TextInput
              id="password"
              type="password"
              class="p-3 mt-1 block w-full bg-gray-100 border-gray-300 rounded-full hover:border-[#8e2de2] focus:border-[#8e2de2] focus:ring-[#8e2de2] focus:ring-opacity-50 transition-all"
              v-model="form.password"
              required
              autocomplete="new-password"
            />

            <InputError class="mt-2" :message="form.errors.password" />
          </div>

          <div class="mt-4">
            <InputLabel
              for="password_confirmation"
              value="Confirm Password"
            />

            <TextInput
              id="password_confirmation"
              type="password"
              class="p-3 mt-1 block w-full bg-gray-100 border-gray-300 rounded-full hover:border-[#8e2de2] focus:border-[#8e2de2] focus:ring-[#8e2de2] focus:ring-opacity-50 transition-all"
              v-model="form.password_confirmation"
              required
              autocomplete="new-password"
            />

            <InputError
              class="mt-2"
              :message="form.errors.password_confirmation"
            />
          </div>

          <div class="mt-8 flex items-center justify-between">
            <router-link to="/login"
                         class="rounded-md text-sm text-gray-600 underline hover:text-[#8e2de2] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
              Already registered?
            </router-link>

            <PrimaryButton
              type="submit"
              class="ms-4 px-6 py-4 bg-[#8e2de2] text-white hover:bg-[#6d18cc] hover:shadow-xl hover:-translate-y-1.5 rounded-full transition-all duration-300 ease-in-out"
              :class="{ 'opacity-25 cursor-not-allowed': form.processing }"
              :disabled="form.processing"
            >
              Register
            </PrimaryButton>
          </div>
        </form>

        <p class="mt-8 text-sm text-gray-500 text-center">
          By signing up, you agree to our
          <a href="/terms" class="text-[#8e2de2] hover:text-[#6d18cc]">Terms of Service</a> and
          <a href="/privacy" class="text-[#8e2de2] hover:text-[#6d18cc]">Privacy Policy</a>
        </p>
      </div>
    </div>
  </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue';
import axiosInstance from '@/axios';
import { useRouter } from 'vue-router';

import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import InputError from '@/components/InputError.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import {useAuthStore} from "@/stores/auth.js";

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  errors: {
    name: null,
    email: null,
    password: null,
    password_confirmation: null,
  },
  processing: false,
});

const resetErrors = () => {
  form.errors = {
    name: null,
    email: null,
    password: null,
    password_confirmation: null,
  };
};

const submit = async () => {
  resetErrors();
  form.processing = true;

  try {
    const payload = {
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation,
    };

    const response = await axiosInstance.post('/register', payload);

    const { auth_token, user } = response.data;
    localStorage.setItem('auth_token', auth_token);
    authStore.setToken(auth_token);
    authStore.setUser(user);


    // Optionally, set the token as a default header for Axios
    axiosInstance.defaults.headers.common['Authorization'] = `Bearer ${auth_token}`;

    // Redirect to dashboard
    router.push('/dashboard');
  } catch (error) {
    if (error.response && error.response.data && error.response.data.errors) {
      // Populate form errors from backend validation
      form.errors = error.response.data.errors;
    } else {
      // Handle other errors (e.g., network issues)
      console.error('An error occurred:', error);
      alert('An unexpected error occurred. Please try again.');
    }
  } finally {
    form.processing = false;
  }
};

// Handle Social Sign-Up Button Clicks
const signUpWithGoogle = () => {
  // Redirect to your backend's Google OAuth endpoint
  window.location.href = `${axiosInstance.defaults.baseURL}/auth/google`;
};

const signUpWithGitHub = () => {
  // Redirect to your backend's GitHub OAuth endpoint
  window.location.href = `${axiosInstance.defaults.baseURL}/auth/github`;
};
</script>

<style scoped>
body {
  font-family: 'Inter', sans-serif;
}

.animate-fadeIn {
  animation: fadeIn 1.5s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.animate-slideIn {
  animation: slideIn 1s ease-in-out;
}

@keyframes slideIn {
  from {
    transform: translateY(-20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.hover\:shadow-3xl:hover {
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
}
</style>
