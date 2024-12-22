<template>
  <div v-if="file" class="file-preview">
    <img
      v-if="isImage"
      :src="previewUrl"
      alt="Preview"
      class="max-w-xs rounded"
    />
    <video
      v-else-if="isVideo"
      controls
      class="max-w-xs rounded"
      :src="previewUrl"
    ></video>
    <div v-else>
      {{ file.name }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'FileDisplay',
  props: {
    file: {
      type: File,
      required: true,
    },
  },
  data() {
    return {
      previewUrl: null,
    };
  },
  computed: {
    isImage() {
      return this.file.type.startsWith('image/');
    },
    isVideo() {
      return this.file.type.startsWith('video/');
    },
  },
  mounted() {
    this.previewUrl = URL.createObjectURL(this.file);
  },
  beforeUnmount() {
    if (this.previewUrl) {
      URL.revokeObjectURL(this.previewUrl);
    }
  },
};
</script>
