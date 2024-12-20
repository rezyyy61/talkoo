<!-- src/components/VoiceRecorder.vue -->
<template>
  <div class="relative group">
    <button
      class="focus:outline-none transition-colors duration-200 p-2"
      aria-label="Record Voice Message"
      @click="toggleRecording"
    >
      <!-- Custom Microphone SVG -->
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-6 w-6"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        :class="store.isRecording ? 'text-red-500' : 'text-gray-400 hover:text-gray-200'"
      >
        <path d="M12 1a3 3 0 00-3 3v7a3 3 0 006 0V4a3 3 0 00-3-3z" />
        <path d="M5 10v2a7 7 0 0014 0v-2" />
        <path d="M12 19v4" />
        <path d="M8 23h8" />
      </svg>
    </button>
  </div>
</template>

<script>
import { ref, onBeforeUnmount } from 'vue';
import { useVoiceMessagesStore } from '@/stores/voiceMessages';

export default {
  name: 'VoiceRecorder',
  setup() {
    const store = useVoiceMessagesStore();
    const mediaRecorder = ref(null);
    const audioChunks = ref([]);

    const toggleRecording = async () => {
      if (store.isRecording) {
        await stopRecording();
      } else {
        await startRecording();
      }
      // Toggle the recording state based on action outcome
      store.setRecordingState(!store.isRecording);
    };

    const startRecording = async () => {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder.value = new MediaRecorder(stream);
        audioChunks.value = [];

        mediaRecorder.value.addEventListener('dataavailable', (event) => {
          audioChunks.value.push(event.data);
        });

        mediaRecorder.value.start();
      } catch (error) {
        console.error('Error accessing microphone:', error);
        alert('Unable to access your microphone. Please check permissions.');
      }
    };

    const stopRecording = () => {
      return new Promise((resolve) => {
        if (mediaRecorder.value && mediaRecorder.value.state !== 'inactive') {
          mediaRecorder.value.addEventListener('stop', () => {
            const audioBlob = new Blob(audioChunks.value, { type: 'audio/webm' });
            const audioURL = URL.createObjectURL(audioBlob);
            store.addVoiceMessage(audioBlob, audioURL);
            resolve();
          });
          mediaRecorder.value.stop();
        } else {
          resolve();
        }
      });
    };

    onBeforeUnmount(() => {
      if (mediaRecorder.value && mediaRecorder.value.state !== 'inactive') {
        mediaRecorder.value.stop();
      }
    });

    return {
      toggleRecording,
      store, // Expose store for template binding
    };
  },
};
</script>

<style scoped>
.text-red-500 {
  color: #f56565;
}
.text-gray-400:hover {
  color: #a0aec0;
}

.rounded-full {
  border-radius: 9999px;
}

.h-6 {
  height: 1.5rem; /* 24px */
}

.w-6 {
  width: 1.5rem; /* 24px */
}

.relative {
  position: relative;
}

.focus\:outline-none:focus {
  outline: none;
}
</style>
