<!-- src/components/Recording.vue -->
<template>
  <div class="recording-container">
    <!-- Display Timer and Moving Line During Recording -->
    <div v-if="isRecording" class="recording-indicator mx-2">
      <div class="moving-line-container">
        <div class="moving-line" :style="{ width: movingLineWidth + '%' }"></div>
      </div>
      <span class="timer">{{ formattedTime }}</span>
    </div>

    <!-- Display Waveform After Recording -->
    <div v-else-if="latestAudioURL" class="waveform-container">
      <button @click="togglePlay" class="play-button w-8 h-8" aria-label="Play/Pause">
        <svg v-if="!isPlaying" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path d="M14 5l7 7-7 7V5z" />
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path d="M6 4h4v16H6V4zM14 4h4v16h-4V4z" />
        </svg>
      </button>
      <div ref="waveform" class="waveform px-4"></div>

      <span class="duration text-sm text-white pr-2">{{ formattedDuration }}</span>

      <!-- Remove Button on the Right -->
      <button @click="removeRecording" class="remove-button w-8 h-8" aria-label="Remove Voice Message">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18" />
          <line x1="6" y1="6" x2="18" y2="18" />
        </svg>
      </button>
    </div>

    <!-- Placeholder When Not Recording and No Audio -->
    <div v-else class="placeholder">
      <p>Ready to record your voice message.</p>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useVoiceMessagesStore } from '@/stores/voiceMessages';
import WaveSurfer from 'wavesurfer.js';

export default {
  name: 'Recording',
  setup() {
    const store = useVoiceMessagesStore();

    // Reactive properties
    const isRecording = computed(() => store.isRecording);
    const latestAudioURL = computed(() => store.latestVoiceMessage?.audioURL || null);
    const latestAudioBlob = computed(() => store.latestVoiceMessage?.audioBlob || null);

    // Timer related (now counting milliseconds)
    const timer = ref(0);
    let timerInterval = null;

    const formattedTime = computed(() => {
      const totalMilliseconds = timer.value;
      const minutes = Math.floor(totalMilliseconds / 60000);
      const seconds = Math.floor((totalMilliseconds % 60000) / 1000);
      const milliseconds = Math.floor((totalMilliseconds % 1000) / 10); // Display two decimal places

      return `${minutes}:${seconds < 10 ? '0' : ''}${seconds}:${milliseconds < 10 ? '0' : ''}${milliseconds}`;
    });

    // Duration after recording
    const formattedDuration = computed(() => {
      const totalMilliseconds = timer.value;
      const minutes = Math.floor(totalMilliseconds / 60000);
      const seconds = Math.floor((totalMilliseconds % 60000) / 1000);
      const milliseconds = Math.floor((totalMilliseconds % 1000) / 10); // Display two decimal places

      return `${minutes}:${seconds < 10 ? '0' : ''}${seconds}:${milliseconds < 10 ? '0' : ''}${milliseconds}`;
    });

    // Moving line related
    const movingLineWidth = ref(0);
    let movingLineInterval = null;

    // WaveSurfer instance
    const waveform = ref(null);
    let waveSurferInstance = null;
    const isPlaying = ref(false);

    /**
     * Starts the timer by incrementing `timer` every 100ms.
     */
    const startTimer = () => {
      timer.value = 0;
      if (timerInterval) clearInterval(timerInterval);
      timerInterval = setInterval(() => {
        timer.value += 100;
      }, 100);
    };

    /**
     * Stops the timer by clearing the interval.
     */
    const stopTimer = () => {
      clearInterval(timerInterval);
    };

    /**
     * Starts the moving line animation by incrementing `movingLineWidth` every 30ms.
     */
    const startMovingLine = () => {
      movingLineWidth.value = 0;
      if (movingLineInterval) clearInterval(movingLineInterval);
      movingLineInterval = setInterval(() => {
        movingLineWidth.value += 1;
        if (movingLineWidth.value >= 100) {
          movingLineWidth.value = 0;
        }
      }, 30);
    };

    /**
     * Stops the moving line animation by clearing the interval and setting width to 100%.
     */
    const stopMovingLine = () => {
      clearInterval(movingLineInterval);
      movingLineWidth.value = 100;
    };

    /**
     * Renders the waveform using WaveSurfer.js for the provided audio URL.
     * @param {string} audioURL - The URL of the recorded audio.
     */
    const renderWaveform = (audioURL) => {
      if (!waveform.value) {
        return;
      }

      if (waveSurferInstance) {
        waveSurferInstance.destroy();
      }

      waveSurferInstance = WaveSurfer.create({
        container: waveform.value,
        waveColor: '#8f6cdd',
        progressColor: '#4FD1C5',
        height: 30,
        responsive: true,
        barWidth: 10,
        barRadius: 10,
        barGap: 2,
        duration: 22,
        backend: 'MediaElement',
      });

      waveSurferInstance.load(audioURL);

      waveSurferInstance.on('ready', () => {
        const durationSeconds = Math.floor(waveSurferInstance.getDuration());
        timer.value = durationSeconds * 1000;
      });

      waveSurferInstance.on('play', () => {
        isPlaying.value = true;
      });

      waveSurferInstance.on('pause', () => {
        isPlaying.value = false;
      });

      waveSurferInstance.on('finish', () => {
        isPlaying.value = false;
      });
    };

    /**
     * Toggles playback of the waveform.
     */
    const togglePlay = () => {
      if (waveSurferInstance) {
        waveSurferInstance.playPause();
      }
    };

    /**
     * Removes the latest recording from the store.
     */
    const removeRecording = () => {
      if (store.latestVoiceMessage) {
        store.removeVoiceMessage(store.latestVoiceMessage.id);
      }
    };

    /**
     * Watches for changes in `isRecording` and starts/stops timer and moving line accordingly.
     */
    watch(
      isRecording,
      (newVal) => {
        if (newVal) {
          startTimer();
          startMovingLine();
        } else {
          stopTimer();
          stopMovingLine();
          if (latestAudioURL.value) {
            // Ensure DOM is updated before rendering waveform
            nextTick(() => {
              renderWaveform(latestAudioURL.value);
            });
          }
        }
      },
      { immediate: true } // Ensures the watcher runs immediately upon mounting
    );

    /**
     * Watches for changes in `latestAudioURL` to render the waveform when a new recording is available.
     */
    watch(latestAudioURL, (newURL) => {
      if (!isRecording.value && newURL) {
        // Ensure DOM is updated before rendering waveform
        nextTick(() => {
          renderWaveform(newURL);
        });
      }
    });

    /**
     * Lifecycle hook called when the component is mounted.
     */
    onMounted(() => {
      if (latestAudioURL.value && !isRecording.value) {
        // Ensure DOM is updated before rendering waveform
        nextTick(() => {
          renderWaveform(latestAudioURL.value);
        });
      }
    });

    /**
     * Lifecycle hook called before the component is unmounted.
     */
    onBeforeUnmount(() => {
      clearInterval(timerInterval);
      clearInterval(movingLineInterval);
      if (waveSurferInstance) {
        waveSurferInstance.destroy();
      }
    });
    console.log(store.getAllVoiceMessages)

    return {
      isRecording,
      formattedTime,
      formattedDuration,
      movingLineWidth,
      waveform,
      isPlaying,
      togglePlay,
      latestAudioURL,
      removeRecording,
      timer,
    };
  },
};
</script>

<style scoped>
.recording-container {
  display: flex;
  align-items: center;
  width: 100%;
  height: 40px;
  background-color: #38B2AC;
  color: white;
  border-radius: 9999px;
  padding: 0 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.recording-indicator {
  display: flex;
  align-items: center;
  flex-grow: 1;
}

.timer {
  font-size: 0.9rem;
  margin-left: 12px;
  white-space: nowrap;
}

.moving-line-container {
  width: 90%;
  height: 6px;
  background-color: #E2E8F0;
  border-radius: 3px;
  overflow: hidden;
}

.moving-line {
  height: 100%;
  background-color: #F56565;
  transition: width 0.03s linear;
}

.waveform-container {
  flex-grow: 1;
  display: flex;
  align-items: center;
  margin: 4px;
  padding: 4px;
  position: relative;
}

.waveform {
  width: 90%;
  height: 30px;
}

.play-button {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 50%;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}
.play-button :hover {
  background: rgba(255, 255, 255, 0.3);
}

.play-button  svg {
  width: 20px;
  height: 20px;
}

.duration {
  font-size: 0.9rem;
  color: #ffffff;
  margin-right: 8px;
}

.remove-button {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 50%;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}

.remove-button:hover {
  background: rgba(255, 255, 255, 0.3);
}

.remove-button svg {
  width: 20px;
  height: 20px;
}

.placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  color: #A0AEC0;
}

/* Optional: Add media queries for responsiveness */
@media (max-width: 600px) {
  .recording-container {
    flex-direction: column;
    height: auto;
    padding: 12px;
  }

  .recording-indicator {
    flex-direction: column;
    align-items: stretch;
    width: 100%;
  }

  .timer {
    margin-left: 0;
    margin-top: 8px;
    text-align: center;
  }
}
</style>
