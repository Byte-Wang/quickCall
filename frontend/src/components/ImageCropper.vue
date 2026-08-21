<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import Cropper from 'cropperjs'
import 'cropperjs/dist/cropper.css'

const props = defineProps<{
  src: string
  mime?: string
  fileName?: string
}>()

const emit = defineEmits<{
  (e: 'confirm', file: File): void
  (e: 'close'): void
}>()

const imgRef = ref<HTMLImageElement | null>(null)
const processing = ref(false)

let cropper: Cropper | null = null

function init() {
  const img = imgRef.value
  if (!img || cropper) return

  cropper = new Cropper(img, {
    aspectRatio: 1,
    viewMode: 1,
    dragMode: 'move',
    autoCropArea: 1,
    background: false,
    responsive: true,
    restore: false,
    guides: true,
    center: true,
    highlight: false,
    cropBoxMovable: true,
    cropBoxResizable: true,
    toggleDragModeOnDblclick: false,
  })
}

onMounted(async () => {
  await nextTick()
  const img = imgRef.value
  // 图片可能已被缓存并立即加载完成，此时不会再次触发 load 事件
  if (img && img.complete && img.naturalWidth > 0) {
    init()
  }
})

onBeforeUnmount(() => {
  cropper?.destroy()
  cropper = null
})

function confirm() {
  if (!cropper || processing.value) return
  processing.value = true

  const canvas = cropper.getCroppedCanvas({ width: 512, height: 512 })
  const type = props.mime || 'image/jpeg'

  canvas.toBlob(
    (blob) => {
      processing.value = false
      if (!blob) {
        emit('close')
        return
      }
      const file = new File([blob], props.fileName || 'avatar.jpg', { type })
      emit('confirm', file)
    },
    type,
    0.9,
  )
}
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
      <div
        class="absolute inset-0 bg-ink/60 backdrop-blur-sm"
        @click="emit('close')"
      ></div>

      <div
        class="relative w-full bg-surface rounded-2xl shadow-pop overflow-hidden"
        style="max-width: 28rem"
      >
        <div
          class="flex items-center justify-between px-5 py-4 border-b border-line"
        >
          <h3 class="font-semibold text-lg">裁剪头像（1:1）</h3>
        </div>

        <div class="cropper-wrap">
          <img ref="imgRef" :src="src" alt="裁剪图片" class="block" @load="init" />
        </div>

        <div class="flex gap-3 p-4">
          <button
            type="button"
            class="flex-1 rounded-xl border border-line py-2.5 text-sm font-medium hover:bg-paper transition-colors"
            @click="emit('close')"
          >
            取消
          </button>
          <button
            type="button"
            class="flex-1 rounded-xl bg-accent text-white py-2.5 text-sm font-medium hover:bg-accent-dark transition-colors disabled:opacity-60"
            :disabled="processing"
            @click="confirm"
          >
            确定
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.cropper-wrap {
  height: 20rem;
  width: 100%;
  background: #000;
}

.cropper-wrap img {
  max-width: 100%;
}
</style>
