<template>
  <div class="relative group">
    <button
      class="focus:outline-none transition-colors duration-200 p-2"
      aria-label="Send Voice Message"
      @click="toggleRecording"
    >

      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-6 w-6"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        :class="isRecording ? 'text-red-500' : 'text-gray-400 hover:text-gray-200'"
      >
        <path d="M12 1a3 3 0 00-3 3v7a3 3 0 006 0V4a3 3 0 00-3-3z" />
        <path d="M5 10v2a7 7 0 0014 0v-2" />
        <path d="M12 19v4" />
        <path d="M8 23h8" />
      </svg>
    </button>

    <div v-if="isRecording" class="mt-2 w-full">

      <div class="text-center text-red-500 mb-1" aria-live="polite">
        {{ formattedTime }}
      </div>

      <!-- نوار پیشرفت انیمیشنی -->
      <div class="rounded-full h-4 overflow-hidden relative">
        <div class="animated-bar"></div>
      </div>
    </div>


    <div v-if="audioURL" class="mt-4">
      <div id="waveform"></div>
      <button @click="playPause" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded">
        <svg
          v-if="!isPlaying"
          class="w-3 h-3 text-gray-800 dark:text-white"
          xmlns="http://www.w3.org/2000/svg"
          fill="currentColor"
          viewBox="0 0 12 16"
        >
          <path d="M3 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2Zm7 0H9a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2Z"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script>
import WaveSurfer from 'wavesurfer.js';

export default {
  name: 'VoiceRecorder',
  data() {
    return {
      isRecording: false,
      elapsedTime: 0,
      timerInterval: null,
      mediaRecorder: null,
      audioChunks: [],
      audioURL: null,
      wavesurfer: null,
    };
  },
  computed: {
    formattedTime() {
      const minutes = Math.floor(this.elapsedTime / 60)
        .toString()
        .padStart(2, '0');
      const seconds = (this.elapsedTime % 60).toString().padStart(2, '0');
      return `${minutes}:${seconds}`;
    },
  },
  methods: {
    async toggleRecording() {
      if (this.isRecording) {
        const audioBlob = await this.stopRecording();
        if (audioBlob) {
          this.audioURL = URL.createObjectURL(audioBlob);
          console.log('Recorded Audio Blob:', audioBlob);
          this.$emit('record-stopped', audioBlob);

          this.$nextTick(() => {
            this.initializeWaveSurfer();
          });
        }
      } else {
        await this.startRecording();
        this.$emit('record-started');
      }
      this.isRecording = !this.isRecording;
    },
    async startRecording() {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        this.mediaRecorder = new MediaRecorder(stream);
        this.audioChunks = [];

        this.mediaRecorder.addEventListener('dataavailable', event => {
          this.audioChunks.push(event.data);
        });

        this.mediaRecorder.start();

        this.elapsedTime = 0;
        this.timerInterval = setInterval(() => {
          this.elapsedTime += 1;
        }, 1000);
      } catch (error) {
        console.error('Error accessing microphone:', error);
        alert('Unable to access microphone. Please check permissions.');
      }
    },
    stopRecording() {
      return new Promise(resolve => {
        if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
          this.mediaRecorder.addEventListener('stop', () => {
            const audioBlob = new Blob(this.audioChunks, { type: 'audio/webm' });
            this.audioChunks = [];
            resolve(audioBlob);
          });
          this.mediaRecorder.stop();

          // توقف تایمر
          clearInterval(this.timerInterval);
          this.timerInterval = null;
        } else {
          resolve(null);
        }
      });
    },
    initializeWaveSurfer() {
      if (this.wavesurfer) {
        this.wavesurfer.destroy();
      }
      this.wavesurfer = WaveSurfer.create({
        container: '#waveform',
        waveColor: 'rgb(200, 0, 200)',
        progressColor: 'rgb(100, 0, 100)',
        barWidth: 10,
        barRadius: 10,
        barGap: 2,
        responsive: true,
        height: 100,
      });

      this.wavesurfer.load(this.audioURL);

      this.wavesurfer.on('ready', () => {
        console.log('WaveSurfer is ready');
      });

      this.wavesurfer.on('error', e => {
        console.error('WaveSurfer error:', e);
      });
    },
    playPause() {
      if (this.wavesurfer) {
        this.wavesurfer.playPause();
      }
    },
  },
  beforeDestroy() {
    if (this.timerInterval) {
      clearInterval(this.timerInterval);
    }
    if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
      this.mediaRecorder.stop();
    }
    if (this.audioURL) {
      URL.revokeObjectURL(this.audioURL);
    }
    if (this.wavesurfer) {
      this.wavesurfer.destroy();
    }
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

.bg-gray-200 {
  background-color: #e2e8f0;
}

.rounded-full {
  border-radius: 9999px;
}

.h-4 {
  height: 1rem;
}

.overflow-hidden {
  overflow: hidden;
}

.relative {
  position: relative;
}

.animated-bar {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.animated-bar::before {
  content: '';
  position: absolute;
  top: 0;
  left: 50%;
  width: 2px;
  height: 100%;
  background-color: #f56565;
  transform: translateX(-50%);
  animation: moveUpDown 1s ease-in-out infinite;
}


@keyframes moveUpDown {
  0% {
    transform: translate(-50%, 0%);
  }
  50% {
    transform: translate(-50%, 100%);
  }
  100% {
    transform: translate(-50%, 0%);
  }
}


audio {
  width: 100%;
  outline: none;
}


#waveform {
  width: 100%;
  height: 100px;
  margin-bottom: 10px;
}
</style>

