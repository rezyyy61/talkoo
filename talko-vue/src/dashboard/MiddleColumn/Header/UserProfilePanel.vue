<template>
  <div>
    <div class="middle-header bg-[#8a949d] h-[120px] flex items-center justify-between px-6 text-white rounded-t-xl">
      <span class="circle-indicator"></span>
      <h2 v-if="!isEditing" class="text-2xl font-extrabold text-white tracking-wide capitalize ml-2">
        Profile
      </h2>
      <h2 v-else class="text-2xl font-extrabold text-white tracking-wide capitalize ml-2">
        Edit Profile
      </h2>
      <button @click="emitClose" class="text-xl hover:text-gray-300">&times;</button>
    </div>

    <div class="bg-white shadow-xl rounded-2xl text-gray-900 w-full h-screen p-6">
      <div v-if="!isEditing">
        <!-- Profile View Content -->
        <div class="flex items-center max-sm:flex-col bg-gray-100 gap-4 rounded-lg overflow-hidden hover:scale-[1.02] transition-all">
          <!-- Conditional rendering for avatar -->
          <template v-if="user.profile.avatarImage">
            <img
              :src="`/storage/${user.profile.avatarImage}`"
              alt="User Avatar"
              class="w-[200px] sm:h-60 object-cover"
            />
          </template>
          <template v-else>
            <div
              :style="{ backgroundColor: user.profile.avatarColor || '#ccc' }"
              class="w-[250px] sm:h-60 flex items-center justify-center text-white font-bold text-lg"
            >
              {{ user.name ? user.name.charAt(0).toUpperCase() : '?' }}
            </div>
          </template>

          <div class="p-4">
            <h4 class="text-gray-800 text-base font-bold">{{ user.name || 'Anonymous' }}</h4>
            <p class="text-blue-500 text-xs font-bold mt-1">@{{ user.profile.userId }}</p>

            <div class="mt-4">
              <p class="text-gray-600 text-sm leading-relaxed">
                bio: {{ user.profile.bio }}
              </p>
            </div>
          </div>
        </div>


        <!-- Settings Section -->
        <div v-if="isCurrentUser" class="p-4 mt-6">
          <h3 class="text-lg font-bold text-gray-800 mb-4">Settings</h3>
          <ul class="space-y-4">
            <li class="flex items-center cursor-pointer" @click="startEditing">
              <!-- Edit Icon -->
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700 mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm1 4H7a2 2 0 00-2 2v1h10v-1a2 2 0 00-2-2z" />
              </svg>
              <span class="text-gray-700">Edit Profile</span>
            </li>
            <li class="flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700 mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1118.878 6.196a9 9 0 01-13.757 11.608zM12 2v4m0 12v4m8-8h4M4 12H0" />
              </svg>
              <span class="text-gray-700">Privacy Settings</span>
            </li>
            <li class="flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700 mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.75v2.5a2.75 2.75 0 005.5 0v-2.5m-6 2.5a3 3 0 00-3 3v5a3 3 0 003 3v1.5a1.5 1.5 0 003 0V16.25a3 3 0 003-3v-5a3 3 0 00-3-3v-2.5" />
              </svg>
              <span class="text-gray-700">Account Security</span>
            </li>
            <li class="flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700 mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11m-6-4h6m-7 8h7m-9-4h4m-4 8h7m4-8h4M4 4h16c.667 0 1.333.333 2 1v16c0 .667-.333 1.333-1 2H4c-.667 0-1.333-.333-2-1V5c0-.667.333-1.333 1-2z" />
              </svg>
              <span class="text-gray-700">Notification Preferences</span>
            </li>
          </ul>
        </div>

        <!-- Logout Button -->
        <div v-if="isCurrentUser" class="text-center mt-4">
          <button @click="logout" class="px-6 py-2 bg-red-500 text-white rounded-full shadow-md hover:bg-red-600 transition mt-2">Logout</button>
        </div>
      </div>

      <!-- Edit Profile Component -->
      <EditProfile
        v-else
        :user="user"
        @save="handleSave"
        @cancel="stopEditing"
      />
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useAuthStore } from '@/stores/auth.js';
import EditProfile from "@/Layouts/peofile/EditProfile.vue";

const emit = defineEmits(['close']);

const props = defineProps({
  user: {
    type: Object,
    required: false,
    default: () => ({}),
  },
});

const authStore = useAuthStore();
const isEditing = ref(false);

const isCurrentUser = computed(() => authStore.user?.id === props.user?.id);

const emitClose = () => {
  emit('close');
};

const startEditing = () => {
  isEditing.value = true;
};

const stopEditing = () => {
  isEditing.value = false;
};

const logout = async () => {
  await authStore.logOut();
  window.location.reload();
  await router.push('/login');
};

const handleSave = (updatedUser) => {
  Object.assign(props.user, updatedUser);
  stopEditing();
};
</script>

<style scoped>
.shadow-xl {
  box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
}
</style>
