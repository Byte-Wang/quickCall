<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuth } from '@/composables/useAuth'
import ToastHost from '@/components/ToastHost.vue'

const { loadMe } = useAuth()

onMounted(loadMe)
</script>

<template>
  <router-view />
  <ToastHost />
</template>
