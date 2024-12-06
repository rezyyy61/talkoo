<!-- src/views/Home.vue -->
<template>
  <div>
    <h1>Home</h1>
    <Message />
  </div>
</template>

<script>
import Message from '../components/Message.vue';

export default {
  name: 'Home',
  components: {
    Message,
  },
};
</script>

<style scoped>
h1 {
  color: #34495e;
}
</style>
