<script setup lang="ts">
import { useToast } from '@/composables/useToast'
import { CircleCheck, CircleAlert, Info } from 'lucide-vue-next'

const { toasts } = useToast()
</script>

<template>
  <Teleport to="body">
    <div
      class="fixed top-5 left-1/2 -translate-x-1/2 z-[120] flex flex-col items-center gap-2 pointer-events-none"
    >
      <TransitionGroup name="toast">
        <div
          v-for="t in toasts"
          :key="t.id"
          class="flex items-center gap-2 rounded-full px-4 py-2.5 text-sm font-medium shadow-pop pointer-events-auto"
          :class="
            t.type === 'success'
              ? 'bg-ink text-white'
              : t.type === 'error'
                ? 'bg-accent text-white'
                : 'bg-surface text-ink'
          "
        >
          <CircleCheck v-if="t.type === 'success'" class="w-4 h-4" />
          <CircleAlert v-else-if="t.type === 'error'" class="w-4 h-4" />
          <Info v-else class="w-4 h-4" />
          {{ t.message }}
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>
