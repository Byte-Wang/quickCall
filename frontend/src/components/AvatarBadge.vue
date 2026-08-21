<script setup lang="ts">
import { computed } from 'vue'
import { nameInitials, phoneToColor } from '@/lib/avatar'
import { apiUrl } from '@/lib/config'

const props = withDefaults(
  defineProps<{
    name: string
    phone: string
    avatar?: string
    size?: number
    fontSize?: number
  }>(),
  {
    avatar: '',
    size: 56,
    fontSize: 18,
  },
)

const bg = computed(() =>
  props.avatar ? 'transparent' : phoneToColor(props.phone),
)

const initials = computed(() => nameInitials(props.name))

const avatarSrc = computed(() => (props.avatar ? apiUrl(props.avatar) : ''))
</script>

<template>
  <div
    class="relative flex items-center justify-center shrink-0 select-none rounded-full overflow-hidden"
    :style="{
      width: `${size}px`,
      height: `${size}px`,
      backgroundColor: bg,
      boxShadow: avatar ? 'inset 0 0 0 1px rgba(0,0,0,0.06)' : 'none',
    }"
  >
    <img
      v-if="avatar"
      :src="avatarSrc"
      :alt="name"
      class="w-full h-full object-cover"
    />
    <span
      v-else
      class="font-semibold leading-none text-white"
      :style="{ fontSize: `${fontSize}px` }"
    >
      {{ initials }}
    </span>
  </div>
</template>
