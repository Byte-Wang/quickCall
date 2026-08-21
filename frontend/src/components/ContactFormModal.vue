<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import type { Contact } from '@/types'
import { api } from '@/lib/api'
import { toast } from '@/composables/useToast'
import BaseModal from './BaseModal.vue'
import AvatarBadge from './AvatarBadge.vue'
import ImageCropper from './ImageCropper.vue'
import { Upload } from 'lucide-vue-next'

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
  })
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
