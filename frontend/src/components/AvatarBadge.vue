<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { nameInitials, phoneToColor } from '@/lib/avatar'
import { apiUrl } from '@/lib/config'
import { cachedImageUrl } from '@/lib/avatarCache'

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

const imgSrc = ref('')

async function resolveAvatar() {
  if (!props.avatar) {
    imgSrc.value = ''
    return
  }

  const url = apiUrl(props.avatar)
  try {
    imgSrc.value = await cachedImageUrl(url)
  } catch {
    // 缓存/下载失败时回退到原始地址，交给浏览器直接加载
    imgSrc.value = url
  }
}

function revoke() {
  if (imgSrc.value.startsWith('blob:')) {
    URL.revokeObjectURL(imgSrc.value)
  }
}

onMounted(resolveAvatar)
watch(() => props.avatar, () => {
  revoke()
  resolveAvatar()
})
onUnmounted(revoke)
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
      v-if="avatar && imgSrc"
      :src="imgSrc"
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
