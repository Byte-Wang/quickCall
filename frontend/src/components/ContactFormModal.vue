<script setup lang="ts">
import { onMounted, onUnmounted, reactive, ref } from 'vue'
import type { Contact } from '@/types'
import { api } from '@/lib/api'
import { toast } from '@/composables/useToast'
import { apiUrl } from '@/lib/config'
import BaseModal from './BaseModal.vue'
import AvatarBadge from './AvatarBadge.vue'
import ImageCropper from './ImageCropper.vue'
import { Mic, Square, Upload } from 'lucide-vue-next'

const props = defineProps<{
  contact: Contact | null
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'save', payload: {
    name: string
    phone: string
    avatar: string
    bg_color: string
    font_size: number | null
    speak_enabled: boolean
    audio_url: string
    audio_trigger: 'name' | 'avatar' | 'longpress'
  }): void
}>()

const form = reactive({
  name: '',
  phone: '',
  avatar: '',
  bg_color: '#ff5a36',
})

const useCustomBg = ref(false)
const useCustomFont = ref(false)
const fontSize = ref(24)
const uploading = ref(false)
const saving = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)
const cropSrc = ref('')
const cropMime = ref('image/jpeg')
const cropName = ref('avatar.jpg')

// 朗读名字相关
const useSpeak = ref(false)
const audioUrl = ref('')
const audioTrigger = ref<'name' | 'avatar' | 'longpress'>('name')
const audioUploading = ref(false)
const recording = ref(false)
const audioFileInput = ref<HTMLInputElement | null>(null)

let mediaRecorder: MediaRecorder | null = null
let recordedChunks: BlobPart[] = []
let recordMime = ''

let cropUrl: string | null = null

const isEdit = () => !!props.contact

onMounted(() => {
  if (props.contact) {
    form.name = props.contact.name
    form.phone = props.contact.phone
    form.avatar = props.contact.avatar
    form.bg_color = props.contact.bg_color || '#ff5a36'
    useCustomBg.value = !!props.contact.bg_color
    useCustomFont.value = props.contact.font_size != null
    fontSize.value = props.contact.font_size ?? 24
    useSpeak.value = props.contact.speak_enabled === true
    audioUrl.value = props.contact.audio_url || ''
    audioTrigger.value = props.contact.audio_trigger || 'name'
  }
})

onUnmounted(() => {
  if (mediaRecorder && recording.value) {
    try {
      mediaRecorder.stop()
    } catch {
      // 忽略已停止状态的异常
    }
  }
})

function onPickFile(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0]
  input.value = ''
  if (!file) return

  cropMime.value = file.type || 'image/jpeg'
  cropName.value = file.name || 'avatar.jpg'
  cropUrl = URL.createObjectURL(file)
  cropSrc.value = cropUrl
}

function closeCropper() {
  if (cropUrl) {
    URL.revokeObjectURL(cropUrl)
    cropUrl = null
  }
  cropSrc.value = ''
}

async function onCropped(file: File) {
  if (cropUrl) {
    URL.revokeObjectURL(cropUrl)
    cropUrl = null
  }
  cropSrc.value = ''

  uploading.value = true
  try {
    const data = await api.upload(file)
    form.avatar = data.url
    toast('头像上传成功', 'success')
  } catch (err) {
    toast((err as Error).message, 'error')
  } finally {
    uploading.value = false
  }
}

function submit() {
  if (!form.name.trim()) {
    toast('请填写名称', 'error')
    return
  }
  if (!form.phone.trim()) {
    toast('请填写手机号', 'error')
    return
  }

  saving.value = true
  emit('save', {
    name: form.name.trim(),
    phone: form.phone.trim(),
    avatar: form.avatar,
    bg_color: useCustomBg.value ? form.bg_color : '',
    font_size: useCustomFont.value ? fontSize.value : null,
    speak_enabled: useSpeak.value,
    audio_url: useSpeak.value ? audioUrl.value : '',
    audio_trigger: useSpeak.value ? audioTrigger.value : 'name',
  })
}

function previewUrl(url: string): string {
  return url ? apiUrl(url) : ''
}

async function saveAudioFile(file: File) {
  audioUploading.value = true
  try {
    const data = await api.uploadAudio(file)
    audioUrl.value = data.url
    toast('音频上传成功', 'success')
  } catch (err) {
    toast((err as Error).message, 'error')
  } finally {
    audioUploading.value = false
  }
}

function onPickAudioFile(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0]
  input.value = ''
  if (!file) return
  saveAudioFile(file)
}

function pickRecordMime(): string {
  if (typeof MediaRecorder === 'undefined') return ''
  const candidates = ['audio/webm', 'audio/mp4', 'audio/ogg']
  for (const mime of candidates) {
    if (MediaRecorder.isTypeSupported(mime)) return mime
  }
  return ''
}

async function startRecording() {
  if (!navigator.mediaDevices?.getUserMedia) {
    toast('当前浏览器不支持录音', 'error')
    return
  }

  try {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true })
    recordMime = pickRecordMime()
    mediaRecorder = new MediaRecorder(stream, recordMime ? { mimeType: recordMime } : undefined)
    recordedChunks = []

    mediaRecorder.ondataavailable = (e) => {
      if (e.data && e.data.size > 0) recordedChunks.push(e.data)
    }

    mediaRecorder.onstop = () => {
      stream.getTracks().forEach((t) => t.stop())
      if (!recordedChunks.length) return
      const blob = new Blob(recordedChunks, { type: recordMime || 'audio/webm' })
      const ext = recordMime.includes('mp4') ? 'm4a' : recordMime.includes('ogg') ? 'ogg' : 'webm'
      const file = new File([blob], `recording-${Date.now()}.${ext}`, {
        type: recordMime || 'audio/webm',
      })
      saveAudioFile(file)
    }

    mediaRecorder.start()
    recording.value = true
  } catch {
    toast('无法访问麦克风，请检查权限', 'error')
  }
}

function stopRecording() {
  mediaRecorder?.stop()
  recording.value = false
}

function removeAudio() {
  if (mediaRecorder && recording.value) stopRecording()
  audioUrl.value = ''
}
</script>

<template>
  <BaseModal
    :title="isEdit() ? '编辑号码' : '添加号码'"
    @close="emit('close')"
  >
    <div class="flex flex-col gap-5">
      <div class="flex items-center gap-4">
        <AvatarBadge
          :name="form.name || '预览'"
          :phone="form.phone || '000'"
          :avatar="form.avatar"
          :size="64"
          :font-size="22"
        />
        <div class="flex flex-col gap-2">
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-xl border border-line px-3.5 py-2 text-sm font-medium hover:bg-paper transition-colors disabled:opacity-50"
            :disabled="uploading"
            @click="fileInput?.click()"
          >
            <Upload class="w-4 h-4" />
            {{ uploading ? '上传中…' : form.avatar ? '更换头像' : '上传头像' }}
          </button>
          <button
            v-if="form.avatar"
            type="button"
            class="text-left text-xs text-muted hover:text-accent transition-colors"
            @click="form.avatar = ''"
          >
            移除头像
          </button>
        </div>
      </div>

      <input
        ref="fileInput"
        type="file"
        accept="image/jpeg,image/png,image/webp,image/gif"
        class="hidden"
        @change="onPickFile"
      />

      <ImageCropper
        v-if="cropSrc"
        :src="cropSrc"
        :mime="cropMime"
        :file-name="cropName"
        @confirm="onCropped"
        @close="closeCropper"
      />

      <div class="grid gap-4">
        <label class="grid gap-1.5">
          <span class="text-sm text-muted">名称</span>
          <input
            v-model="form.name"
            type="text"
            placeholder="例如：张三"
            class="rounded-xl border border-line px-3 py-2.5 text-sm outline-none focus:border-accent focus:ring-2 focus:ring-accent/20 transition"
          />
        </label>

        <label class="grid gap-1.5">
          <span class="text-sm text-muted">手机号</span>
          <input
            v-model="form.phone"
            type="tel"
            placeholder="例如：13800138000"
            class="rounded-xl border border-line px-3 py-2.5 text-sm outline-none focus:border-accent focus:ring-2 focus:ring-accent/20 transition"
          />
        </label>

        <div class="flex items-center justify-between">
          <span class="text-sm text-muted">自定义卡片背景色</span>
          <label class="relative inline-flex items-center cursor-pointer">
            <input v-model="useCustomBg" type="checkbox" class="sr-only peer" />
            <div
              class="w-10 h-6 rounded-full bg-line peer-checked:bg-accent transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:w-5 after:h-5 after:transition peer-checked:after:translate-x-4"
            ></div>
          </label>
        </div>

        <label v-if="useCustomBg" class="flex items-center gap-3">
          <span class="text-sm text-muted">背景颜色</span>
          <input
            v-model="form.bg_color"
            type="color"
            class="w-12 h-9 rounded-lg border border-line cursor-pointer bg-transparent"
          />
          <span class="text-xs text-muted">{{ form.bg_color }}</span>
        </label>

        <div class="flex items-center justify-between">
          <span class="text-sm text-muted">自定义字号</span>
          <label class="relative inline-flex items-center cursor-pointer">
            <input v-model="useCustomFont" type="checkbox" class="sr-only peer" />
            <div
              class="w-10 h-6 rounded-full bg-line peer-checked:bg-accent transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:w-5 after:h-5 after:transition peer-checked:after:translate-x-4"
            ></div>
          </label>
        </div>

        <div v-if="useCustomFont" class="flex items-center gap-3">
          <span class="text-sm text-muted">字号</span>
          <input
            v-model.number="fontSize"
            type="range"
            min="12"
            max="48"
            step="1"
            class="flex-1 accent-[var(--c-accent)]"
          />
          <span class="text-sm font-semibold w-8 text-right">{{ fontSize }}</span>
        </div>

        <div class="flex items-center justify-between">
          <span class="text-sm text-muted">朗读名字</span>
          <label class="relative inline-flex items-center cursor-pointer">
            <input v-model="useSpeak" type="checkbox" class="sr-only peer" />
            <div
              class="w-10 h-6 rounded-full bg-line peer-checked:bg-accent transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:w-5 after:h-5 after:transition peer-checked:after:translate-x-4"
            ></div>
          </label>
        </div>

        <div v-if="useSpeak" class="grid gap-4 rounded-xl border border-line p-3">
          <div class="grid gap-2">
            <span class="text-sm text-muted">音频设置</span>
            <input
              ref="audioFileInput"
              type="file"
              accept="audio/*"
              class="hidden"
              @change="onPickAudioFile"
            />
            <div class="flex items-center gap-2">
              <button
                type="button"
                class="flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-line px-3 py-2 text-sm font-medium hover:bg-paper transition-colors disabled:opacity-50"
                :disabled="audioUploading || recording"
                @click="audioFileInput?.click()"
              >
                <Upload class="w-4 h-4" />
                {{ audioUploading ? '上传中…' : '上传音频文件' }}
              </button>
              <button
                v-if="!recording"
                type="button"
                class="flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-line px-3 py-2 text-sm font-medium hover:bg-paper transition-colors disabled:opacity-50"
                :disabled="audioUploading"
                @click="startRecording"
              >
                <Mic class="w-4 h-4" />
                录音
              </button>
              <button
                v-else
                type="button"
                class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-accent px-3 py-2 text-sm font-medium text-white transition-colors"
                @click="stopRecording"
              >
                <Square class="w-4 h-4" />
                停止录音
              </button>
            </div>

            <div v-if="audioUrl" class="flex items-center gap-2">
              <audio :src="previewUrl(audioUrl)" controls class="min-w-0 flex-1"></audio>
              <button
                type="button"
                class="shrink-0 text-xs text-muted hover:text-accent transition-colors"
                @click="removeAudio"
              >
                移除
              </button>
            </div>
          </div>

          <div class="grid gap-2">
            <span class="text-sm text-muted">播放设置</span>
            <div class="grid gap-2">
              <label class="flex items-center gap-2 text-sm">
                <input v-model="audioTrigger" type="radio" value="name" class="accent-[var(--c-accent)]" />
                点击名字
              </label>
              <label class="flex items-center gap-2 text-sm">
                <input v-model="audioTrigger" type="radio" value="avatar" class="accent-[var(--c-accent)]" />
                点击头像
              </label>
              <label class="flex items-center gap-2 text-sm">
                <input v-model="audioTrigger" type="radio" value="longpress" class="accent-[var(--c-accent)]" />
                长按头像
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="flex gap-3 pt-1">
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
          :disabled="saving"
          @click="submit"
        >
          保存
        </button>
      </div>
    </div>
  </BaseModal>
</template>
