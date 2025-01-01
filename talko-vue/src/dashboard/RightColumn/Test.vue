<template>
  <div>
    <h2>Chat with ID: {{ conversationId }}</h2>
    <ul>
      <li v-for="msg in messages" :key="msg.id">{{ msg.content }}</li>
    </ul>
    <input v-model="newMessage" @keyup.enter="onSend">
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useMessageStore } from '@/stores/message';

const messageStore = useMessageStore();
const newMessage = ref('');

const conversationId = computed(() => messageStore.conversationId);
const messages = computed(() => messageStore.messages);

function onSend() {
  // برای پیام خصوصی:
  messageStore.sendMessage({
    type: 'private',
    content: newMessage.value,
    // اگر بخواهید مشخصاً receiver_id یا conversation_id بفرستید
    // می‌توانید اینجا ست کنید
    // receiver_id: messageStore.receiverId,
    // conversation_id: messageStore.conversationId,
  });
  newMessage.value = '';
}
</script>
