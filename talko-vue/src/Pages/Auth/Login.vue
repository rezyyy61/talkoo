
<template>
    <div class="h-screen bg-gradient-to-b from-[#38385f] to-[#2e2d51] text-white flex justify-center items-center overflow-hidden">
        <div class="w-full max-w-screen-xl lg:flex shadow-2xl rounded-3xl transform transition-all duration-500 hover:scale-105">
            <!-- Left Panel -->
            <div class="hidden lg:flex lg:w-1/2 bg-cover bg-center bg-no-repeat"
                 style="background-image: url('#');">
                <div class="flex items-center justify-center w-full h-full bg-gradient-to-t from-[#38385f] via-transparent to-[#38385f]">
                    <h2 class="text-5xl font-extrabold text-white drop-shadow-lg">Welcome Back!</h2>
                </div>
            </div>

            <!-- Right Panel -->
            <div class="w-full lg:w-1/2 bg-white text-gray-800 p-8 lg:p-16 flex items-center justify-center rounded-r-3xl h-full">
                <div class="w-full">
                    <div class="flex justify-center mb-8">
                        <img src="#" class="w-28 shadow-lg rounded-full border-4" alt="Logo" />
                    </div>
                    <h1 class="text-4xl font-extrabold text-center mb-6 text-[#38385f]">Log in to Your Account</h1>

                    <div v-if="status" class="mb-4 text-sm font-medium text-green-600 text-center">
                        {{ status }}
                    </div>

                    <!-- Email Login Form -->
                    <form @submit.prevent="submit">
                        <div>
                            <InputLabel for="email" value="Email" />

                            <TextInput
                                id="email"
                                type="email"
                                class="mt-1 block w-full bg-gray-100 border-gray-300"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                            />

                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="password" value="Password" />

                            <TextInput
                                id="password"
                                type="password"
                                class="mt-1 block w-full bg-gray-100 border-gray-300"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                            />

                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div class="mt-4 block">
                            <label class="flex items-center">
                                <Checkbox name="remember" v-model:checked="form.remember" />
                                <span class="ms-2 text-sm text-gray-600">Remember me</span>
                            </label>
                        </div>

                        <div class="mt-8 flex items-center justify-between">
                          <router-link
                            v-if="canResetPassword"
                            to="/forgotPassword"
                            class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                          >
                            Forgot your password?
                          </router-link>

                            <PrimaryButton
                                class="ms-4 px-6 py-3 bg-[#6c63ff] text-white hover:bg-[#5a54e6] hover:shadow-lg rounded-lg transition-all duration-300 ease-in-out"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Log in
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Checkbox from "@/components/Checkbox.vue";
import InputError from "@/components/InputError.vue";
import InputLabel from "@/components/InputLabel.vue";
import PrimaryButton from "@/components/PrimaryButton.vue";
import TextInput from "@/components/TextInput.vue";
import {useRouter} from "vue-router";
import axiosInstance from '@/axios';
import {useAuthStore} from "@/stores/auth.js";
import {reactive, ref} from "vue";

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  email: '',
  password: '',
  remember: false,
  errors: {
    email: null,
    password: null,
  },
  processing: false,
});

const status = ref('');
const canResetPassword = ref(true);

const resetErrors = () => {
  form.errors = {
    email: null,
    password: null,
  };
};

const submit = async () => {
  resetErrors();
  form.processing = true;
  status.value = '';

  try {
    const payload = {
      email: form.email,
      password: form.password,
      remember: form.remember,
    };

    const response = await axiosInstance.post('/login', payload);

    const { auth_token, user } = response.data;
    authStore.setToken(auth_token);
    authStore.setUser(user);

    status.value = 'Logged in successfully!';
    router.push('/dashboard');
  } catch (error) {
    if (error.response && error.response.data && error.response.data.errors) {
      form.errors = error.response.data.errors;
    } else {

      console.error('An error occurred:', error);
      status.value = 'An unexpected error occurred. Please try again.';
    }
  } finally {
    form.processing = false;
  }
};
</script>

<style scoped>
body {
    font-family: 'Inter', sans-serif;
}
</style>
