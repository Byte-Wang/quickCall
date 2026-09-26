<script setup lang="ts">
import { ref } from 'vue'
import QRCode from 'qrcode'
import BaseModal from './BaseModal.vue'
import { ArrowLeft, Download, MessageSquare, QrCode } from 'lucide-vue-next'
import { api } from '@/lib/api'
import { toast } from '@/composables/useToast'

const emit = defineEmits<{
  (e: 'close'): void
}>()

const APK_URL = 'https://quickcall.wangdalong.top/quickCallApp-release.apk'

type View = 'choice' | 'android' | 'ios' | 'feedback'
const view = ref<View>('choice')

const iosSteps = [
  {
    text: '使用系统相机扫描拨号页二维码，点击链接选择用浏览器打开。',
    img: '/1.jpg',
  },
  {
    text: '浏览器打开拨号页后，点击右下角三个点，打开菜单。',
    img: '/2.jpg',
  },
  {
    text: '点击菜单中的「分享」按钮。',
    img: '/3.jpg',
  },
  {
    text: '点击分享菜单右下角的「更多」按钮。',
    img: '/4.jpg',
  },
  {
    text: '点击「添加到主屏幕」按钮。',
    img: '/5.jpg',
  },
  {
    text: '在手机桌面就能看到一个一键打开拨号页的快捷图标了。',
    img: '/6.jpg',
  },
]

const qrData = ref('')
const showQr = ref(false)

async function renderQr() {
  try {
    qrData.value = await QRCode.toDataURL(APK_URL, {
      width: 240,
      margin: 1,
      color: { dark: '#201a17', light: '#ffffff' },
    })
  } catch {
    qrData.value = ''
  }
}

function toggleQr() {
  showQr.value = !showQr.value
  if (showQr.value && !qrData.value) renderQr()
}

const feedback = ref('')
const submitting = ref(false)

async function submitFeedback() {
  const content = feedback.value.trim()
  if (!content) {
    toast('请填写反馈内容', 'error')
    return
  }

  submitting.value = true
  try {
    await api.submitFeedback({
      content,
      user_agent: navigator.userAgent,
      platform: navigator.platform ?? '',
      language: navigator.language ?? '',
      screen: `${window.screen.width}x${window.screen.height}`,
      client_time: new Date().toISOString(),
    })
    toast('反馈已提交，感谢你的建议', 'success')
    feedback.value = ''
    view.value = 'choice'
  } catch (err) {
    toast((err as Error).message, 'error')
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <BaseModal title="如何使用" max-width="32rem" @close="emit('close')">
    <div class="max-h-[70vh] overflow-y-auto">
      <!-- 选择手机类型 -->
      <div v-if="view === 'choice'" class="grid gap-3">
        <p class="text-sm text-muted">请选择老人使用的手机类型：</p>
        <button
          type="button"
          class="flex items-center justify-center gap-2 rounded-xl border border-line py-3.5 text-sm font-medium hover:bg-paper transition-colors"
          @click="view = 'android'"
        >
          给老人使用安卓手机
        </button>
        <button
          type="button"
          class="flex items-center justify-center gap-2 rounded-xl border border-line py-3.5 text-sm font-medium hover:bg-paper transition-colors"
          @click="view = 'ios'"
        >
          给老人使用苹果手机
        </button>
        <button
          type="button"
          class="flex items-center justify-center gap-2 rounded-xl border border-dashed border-line py-3 text-sm text-muted hover:bg-paper hover:text-ink transition-colors"
          @click="view = 'feedback'"
        >
          <MessageSquare class="w-4 h-4" />
          意见反馈
        </button>
      </div>

      <!-- 安卓使用说明 -->
      <div v-else-if="view === 'android'" class="grid gap-4">
        <button
          type="button"
          class="flex items-center gap-1 text-sm text-muted hover:text-ink transition-colors"
          @click="view = 'choice'"
        >
          <ArrowLeft class="w-4 h-4" />
          返回
        </button>

        <div class="grid gap-5">
          <div class="flex gap-3">
            <div
              class="shrink-0 w-7 h-7 rounded-full bg-accent/10 text-accent grid place-items-center text-sm font-semibold"
            >
              1
            </div>
            <div class="flex-1 grid gap-2.5">
              <p class="text-sm font-medium">第一步：下载安卓客户端</p>
              <div class="grid grid-cols-2 gap-3">
                <a
                  :href="APK_URL"
                  download
                  class="inline-flex items-center justify-center gap-2 rounded-xl bg-accent text-white px-4 py-2.5 text-sm font-semibold hover:bg-accent-dark transition-colors"
                >
                  <Download class="w-4 h-4" />
                  点击下载
                </a>
                <button
                  type="button"
                  class="inline-flex items-center justify-center gap-2 rounded-xl border border-line px-4 py-2.5 text-sm font-medium hover:bg-paper transition-colors"
                  @click="toggleQr"
                >
                  <QrCode class="w-4 h-4" />
                  扫码下载
                </button>
              </div>

              <div v-if="showQr" class="flex flex-col items-center gap-3 pt-1">
                <div class="bg-white p-3 rounded-2xl border border-line shadow-card">
                  <img
                    v-if="qrData"
                    :src="qrData"
                    alt="扫码下载安卓客户端"
                    class="w-40 h-40"
                  />
                  <div
                    v-else
                    class="w-40 h-40 grid place-items-center text-muted text-sm"
                  >
                    二维码生成中…
                  </div>
                </div>
                <p class="text-xs text-muted">使用手机扫描二维码下载安卓客户端</p>
              </div>
            </div>
          </div>

          <div class="flex gap-3">
            <div
              class="shrink-0 w-7 h-7 rounded-full bg-accent/10 text-accent grid place-items-center text-sm font-semibold"
            >
              2
            </div>
            <div class="flex-1 grid gap-1.5">
              <p class="text-sm font-medium">第二步：扫码打开拨号页</p>
              <p class="text-sm text-muted leading-relaxed">
                安装好客户端后，点击「扫描二维码」，扫描拨号页的二维码打开拨号页即可。
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- 苹果使用说明 -->
      <div v-else-if="view === 'ios'" class="grid gap-4">
        <button
          type="button"
          class="flex items-center gap-1 text-sm text-muted hover:text-ink transition-colors"
          @click="view = 'choice'"
        >
          <ArrowLeft class="w-4 h-4" />
          返回
        </button>

        <div class="grid gap-6">
          <div v-for="(step, i) in iosSteps" :key="i" class="flex gap-3">
            <div
              class="shrink-0 w-7 h-7 rounded-full bg-accent/10 text-accent grid place-items-center text-sm font-semibold"
            >
              {{ i + 1 }}
            </div>
            <div class="flex-1 grid gap-2.5">
              <p class="text-sm font-medium leading-relaxed">{{ step.text }}</p>
              <img
                :src="step.img"
                :alt="step.text"
                class="w-full max-w-[240px] rounded-xl border border-line"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- 意见反馈 -->
      <div v-else class="grid gap-4">
        <button
          type="button"
          class="flex items-center gap-1 text-sm text-muted hover:text-ink transition-colors"
          @click="view = 'choice'"
        >
          <ArrowLeft class="w-4 h-4" />
          返回
        </button>

        <div class="grid gap-3">
          <p class="text-sm text-muted">如有问题或建议，欢迎告诉我们：</p>
          <textarea
            v-model="feedback"
            rows="5"
            placeholder="请描述你遇到的问题或建议…"
            class="rounded-xl border border-line px-3 py-2.5 text-sm outline-none resize-none focus:border-accent focus:ring-2 focus:ring-accent/20 transition"
          ></textarea>
          <button
            type="button"
            class="rounded-xl bg-accent text-white py-3 text-sm font-semibold hover:bg-accent-dark transition-colors disabled:opacity-60"
            :disabled="submitting"
            @click="submitFeedback"
          >
            {{ submitting ? '提交中…' : '提交反馈' }}
          </button>
        </div>
      </div>
    </div>
  </BaseModal>
</template>
