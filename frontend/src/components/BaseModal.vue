<script setup lang="ts">
import { X } from 'lucide-vue-next'

defineProps<{
  title?: string
  maxWidth?: string
}>()

const emit = defineEmits<{
  (e: 'close'): void
}>()
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div
        class="absolute inset-0 bg-ink/40 backdrop-blur-sm"
        @click="emit('close')"
      ></div>

      <div
        class="relative w-full bg-surface rounded-2xl shadow-pop animate-pop overflow-hidden"
        :style="{ maxWidth: maxWidth || '28rem' }"
      >
        <div
          v-if="title"
          class="flex items-center justify-between px-5 py-4 border-b border-line"
        >
          <h3 class="font-semibold text-lg">{{ title }}</h3>
          <button
            type="button"
            class="w-8 h-8 grid place-items-center rounded-full text-muted hover:bg-paper hover:text-ink transition-colors"
            @click="emit('close')"
          >
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="p-5">
          <slot />
        </div>
      </div>
    </div>
  </Teleport>
</template>
