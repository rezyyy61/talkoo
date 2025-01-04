<template>
  <div class="p-6 bg-gray-100 rounded-lg shadow-md relative">
    <!-- Back Arrow -->
    <button
      @click="handleCancel"
      class="absolute top-4 left-4 bg-transparent font-bold text-gray-800 hover:text-blue-500 transition"
      title="Back to Profile"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
           viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
      </svg>
    </button>

    <h3 class="text-lg font-bold text-gray-800 mb-6 mt-6">Edit Your Profile</h3>
    <!-- Avatar Section -->
    <div class="flex justify-center mb-6">
      <div class="relative">
        <!-- If either a new preview or an existing avatar image exists, show it -->
        <div v-if="previewAvatar || editedUser.avatarImage" class="relative">
          <img
            :src="previewAvatar || (editedUser.avatarImage ? `/storage/${editedUser.avatarImage}` : null)"
            alt="User Avatar"
            class="w-60 h-40 rounded-xl object-cover border-2 border-gray-300"
          />

          <!-- Remove Avatar Button -->
          <button
            @click="removeAvatar"
            class="absolute bottom-0 left-0 bg-white text-red-500 rounded-full p-1 cursor-pointer
                   hover:bg-red-500 hover:text-white transition duration-200 shadow-md"
            title="Remove Avatar"
            aria-label="Remove Avatar"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6" />
            </svg>
          </button>
        </div>
        <!-- Otherwise, display placeholder -->
        <div
          v-else
          :style="{ backgroundColor: avatarBgColor }"
          class="w-60 h-40 rounded-xl border-2 border-gray-300 flex items-center justify-center"
        >
          <span class="text-gray-500">No Avatar Selected</span>
        </div>

        <!-- Change Avatar Button -->
        <label
          for="avatarInput"
          class="absolute bottom-0 right-0 bg-white text-blue-500 rounded-full p-1 cursor-pointer
            hover:bg-blue-500 hover:text-white transition duration-200 shadow-md"
          title="Change Profile Picture"
          aria-label="Change Profile Picture"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
               viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
                  d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                  clip-rule="evenodd" />
          </svg>
        </label>
        <input
          type="file"
          id="avatarInput"
          accept="image/*"
          class="hidden"
          @change="onAvatarChange"
        />
      </div>
    </div>

    <!-- Color Picker Section -->
    <div class="mb-6">
      <label for="avatarBgColor" class="block text-gray-700 font-bold mb-2">
        Avatar Background Color
      </label>
      <input
        type="color"
        id="avatarBgColor"
        v-model="avatarBgColor"
        class="w-12 h-12 p-0 border rounded-lg"
        title="Choose a background color for your avatar"
      />
    </div>

    <form @submit.prevent="handleSubmit">
      <!-- Name Field -->
      <div class="mb-4">
        <label for="name" class="block text-gray-700 font-bold mb-2">Name</label>
        <input
          v-model="editedUser.name"
          id="name"
          type="text"
          class="w-full border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <!-- User ID Field -->
      <div class="mb-4">
        <label for="userId" class="block text-gray-700 font-bold mb-2">User ID</label>
        <input
          v-model="editedUser.userId"
          id="userId"
          type="text"
          class="w-full border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <!-- Bio Field -->
      <div class="mb-4">
        <label for="bio" class="block text-gray-700 font-bold mb-2">Bio</label>
        <textarea
          v-model="editedUser.bio"
          id="bio"
          class="w-full border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          rows="4"
          placeholder="Tell us about yourself..."
        ></textarea>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end space-x-4">
        <button
          type="button"
          @click="handleCancel"
          class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition"
        >
          Cancel
        </button>
        <button
          type="submit"
          class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition"
        >
          Save
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { defineEmits, defineProps, reactive, ref, watch } from 'vue';
import { useAuthStore } from '@/stores/auth';

const emit = defineEmits(['save', 'cancel']);
const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
});

const authStore = useAuthStore();

const editedUser = reactive({
  id: props.user.id,
  name: props.user.name,
  email: props.user.email,
  userId: props.user.profile.userId,
  bio: props.user.profile.bio,
  avatarImage: props.user.profile.avatarImage,
  avatarFile: null,

  // IMPORTANT: This is our new field to indicate removal of the image
  removeAvatar: false,
});

const removeAvatar = () => {
  editedUser.avatarFile = null;   // no new file
  editedUser.avatarImage = null;  // for the preview
  editedUser.removeAvatar = true; // the key: triggers deletion
};

const avatarBgColor = ref(props.user.profile.avatarColor || '#ffffff');
const previewAvatar = ref(null);

// Called when the user selects a new avatar file
const onAvatarChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    previewAvatar.value = URL.createObjectURL(file);
    editedUser.avatarFile = file;
    editedUser.removeAvatar = false; // They chose a file, so no removal
  }
};
// Called when user clicks "Save"
const handleSubmit = async () => {
  const payload = {
    name: editedUser.name,
    userId: editedUser.userId,
    bio: editedUser.bio,
    avatarFile: editedUser.avatarFile,
    avatarColor: avatarBgColor.value,

    // The key line: pass along removeAvatar field
    removeAvatar: editedUser.removeAvatar,
  };

  try {
    const response = await authStore.updateProfile(payload);
    emit('save', response.user);
  } catch (error) {
    console.error('Failed to update the profile:', error);
  }
};

// If the user prop changes, reset edited fields
watch(
  () => props.user,
  (newUser) => {
    Object.assign(editedUser, newUser, { avatarFile: null, removeAvatar: false });
    avatarBgColor.value = newUser.profile.avatarColor || '#ffffff';
    previewAvatar.value = null;
  }
);
</script>

<style scoped>
.relative label:hover {
  background-color: rgba(59, 130, 246, 0.8);
}
</style>
