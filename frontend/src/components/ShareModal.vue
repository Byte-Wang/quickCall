<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import QRCode from 'qrcode'
import type { DialPage } from '@/types'
import { toast } from '@/composables/useToast'
import { copyText } from '@/lib/clipboard'
import BaseModal from './BaseModal.vue'
import { Check, Copy } from 'lucide-vue-next'

const props = defineProps<{
  page: DialPage
  contactId?: number
}>()

const emit = defineEmits<{
  (e: 'close'): void
}>()

const qrData = ref('')
const copied = ref(false)

const shareUrl = computed(() => {
  const base = `${location.origin}/#/d/${props.page.slug}`
  return props.contactId ? `${base}/${props.contactId}` : base
})

async function renderQr() {
  try {
    qrData.value = await QRCode.toDataURL(shareUrl.value, {
      width: 240,
      margin: 1,
      color: { dark: '#201a17', light: '#ffffff' },
    })
  } catch {
    qrData.value = ''
  }
}

async function copyLink() {
  try {
    await copyText(shareUrl.value)
    copied.value = true
    toast('链接已复制', 'success')
    setTimeout(() => (copied.value = false), 2000)
  } catch {
    toast('复制失败，请手动复制', 'error')
  }
}

onMounted(renderQr)
watch(() => props.page.slug, renderQr)
</script>

<template>
  <BaseModal :title="contactId ? '分享号码' : '分享拨号页'" @close="emit('close')">
    <div class="flex flex-col items-center gap-5">
      <div
        class="bg-white p-4 rounded-2xl border border-line shadow-card"
        v-if="qrData"
      >
        <img :src="qrData" alt="二维码" class="w-48 h-48" />
      </div>
      <div v-else class="w-48 h-48 grid place-items-center text-muted text-sm">
        二维码生成中…
      </div>

      <div class="w-full flex items-center gap-2">
        <input
          :value="shareUrl"
          readonly
          class="flex-1 min-w-0 rounded-xl border border-line bg-paper/60 px-3 py-2.5 text-sm text-muted outline-none"
        />
        <button
          type="button"
          class="flex items-center gap-1.5 rounded-xl bg-ink text-white px-3.5 py-2.5 text-sm font-medium hover:bg-black transition-colors"
          @click="copyLink"
        >
          <Check v-if="copied" class="w-4 h-4" />
          <Copy v-else class="w-4 h-4" />
          {{ copied ? '已复制' : '复制' }}
        </button>
      </div>

      <p class="text-xs text-muted">
        访问者打开该链接即可看到通讯录并一键拨号
      </p>
    </div>
  </BaseModal>
</template>
