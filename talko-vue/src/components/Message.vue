<!-- src/components/Message.vue -->
<template>
  <div>
    <h1>{{ message }}</h1>
    <p>Count: {{ counter.count }}</p>
    <button @click="counter.increment">Increment</button>
  </div>
</template>

<script>
import axios from '../axios';
import { useCounterStore } from '../stores/counter';
import { ref, onMounted } from 'vue';

export default {
  name: 'Message',
  setup() {
    const counter = useCounterStore();
    const message = ref('');

    const fetchMessage = async () => {
      try {
        const response = await axios.get('/message');
        message.value = response.data.message;
      } catch (error) {
        console.error('Error fetching message:', error);
      }
    };

    onMounted(() => {
      fetchMessage();
    });

    return {
      message,
      counter,
    };
  },
};
</script>

<style scoped>
h1 {
  color: #42b983;
}
</style>
