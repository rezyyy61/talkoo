<!-- src/components/AudioWaveform.vue -->
<template>
  <div class="audio-waveform-container">
    <button
      @click="togglePlay"
      class="play-button"
      :aria-label="isPlaying ? 'Pause Audio' : 'Play Audio'"
    >
      <svg
        v-if="!isPlaying"
        xmlns="http://www.w3.org/2000/svg"
        class="icon play-icon"
        viewBox="0 0 24 24"
        fill="currentColor"
      >
        <path d="M5 3v18l15-9z" />
      </svg>
      <svg
        v-else
        xmlns="http://www.w3.org/2000/svg"
        class="icon pause-icon"
        viewBox="0 0 24 24"
        fill="currentColor"
      >
        <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z" />
      </svg>
    </button>
    <div ref="waveform" class="waveform"></div>
  </div>
</template>

<script>
import WaveSurfer from 'wavesurfer.js';
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';

export default {
  name: 'AudioWaveform',
  props: {
    audioUrl: {
      type: String,
      required: true,
    },
  },
  setup(props) {
    const waveform = ref(null);
    const wavesurfer = ref(null);
    const isPlaying = ref(false);

    const togglePlay = () => {
      if (wavesurfer.value) {
        wavesurfer.value.playPause();
      }
    };

    onMounted(() => {
      wavesurfer.value = WaveSurfer.create({
        container: waveform.value,
        waveColor: '#b4f380',
        progressColor: '#b4f380',
        cursorColor: '#b4f380',
        cursorWidth: 2,
        barWidth: 4,
        barRadius: 2,
        responsive: true,
        height: 40,
        normalize: true,
        backend: 'MediaElement',
      });

      wavesurfer.value.load(props.audioUrl);

      wavesurfer.value.on('ready', () => {
        // Waveform is ready
      });

      wavesurfer.value.on('play', () => {
        isPlaying.value = true;
      });

      wavesurfer.value.on('pause', () => {
        isPlaying.value = false;
      });

      wavesurfer.value.on('finish', () => {
        isPlaying.value = false;
      });

      wavesurfer.value.on('error', (error) => {
        console.error('WaveSurfer Error:', error);
      });
    });

    onBeforeUnmount(() => {
      if (wavesurfer.value) {
        wavesurfer.value.destroy();
      }
    });

    watch(
      () => props.audioUrl,
      (newUrl) => {
        if (wavesurfer.value) {
          wavesurfer.value.load(newUrl);
        }
      }
    );

    return {
      waveform,
      togglePlay,
      isPlaying,
    };
  },
};
</script>

<style scoped>
.audio-waveform-container {
  display: flex;
  align-items: center;
  width: 100%;
}
.play-button {
  margin-top: 15px;
}
.play-button:hover {
  transform: scale(1.05);
}

.play-button:active {
  transform: scale(0.95);
}

.icon {
  width: 24px;
  height: 24px;
  color: #4b871a; /* Indigo-600 */
}

.waveform {
  flex: 1;
  width: 100%;
  min-width: 200px;
  height: 40px;
  padding: 0.5rem;
  box-sizing: border-box;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .waveform {
    height: 50px;
  }
}

@media (max-width: 480px) {
  .waveform {
    height: 40px;
  }

  .play-button {
    padding: 0.4rem;
    margin-right: 0.3rem;
  }

  .icon {
    width: 18px;
    height: 18px;
  }
}
</style>
