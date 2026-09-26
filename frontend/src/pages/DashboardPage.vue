<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { api } from '@/lib/api'
import type { DialPage } from '@/types'
import { useAuth } from '@/composables/useAuth'
import { toast } from '@/composables/useToast'
import BaseModal from '@/components/BaseModal.vue'
import ShareModal from '@/components/ShareModal.vue'
import HowToUseModal from '@/components/HowToUseModal.vue'
import {
  LogOut,
  Pencil,
  PhoneCall,
  Plus,
  Share2,
  Trash2,
  Users,
} from 'lucide-vue-next'

const router = useRouter()
const { user, logout } = useAuth()

const pages = ref<DialPage[]>([])
const loading = ref(true)
const shareTarget = ref<DialPage | null>(null)
const showCreate = ref(false)
const showHowTo = ref(false)
const createName = ref('')

async function load() {
  loading.value = true
  try {
    pages.value = await api.listPages()
  } catch (e) {
    toast((e as Error).message, 'error')
  } finally {
    loading.value = false
  }
}

async function createPage() {
  const name = createName.value.trim()
  if (!name) {
    toast('请输入拨号页名称', 'error')
    return
  }

  try {
    const page = await api.createPage(name)
    toast('创建成功', 'success')
    showCreate.value = false
    createName.value = ''
    router.push(`/admin/page/${page.id}`)
  } catch (e) {
    toast((e as Error).message, 'error')
  }
}

async function removePage(page: DialPage) {
  if (!window.confirm(`确定删除拨号页「${page.name}」吗？`)) return

  try {
    await api.deletePage(page.id)
    toast('已删除', 'success')
    load()
  } catch (e) {
    toast((e as Error).message, 'error')
  }
}

function handleLogout() {
  logout()
  router.replace('/login')
}

onMounted(load)
</script>

<template>
  <div class="min-h-screen">
    <header class="sticky top-0 z-30 bg-paper/80 backdrop-blur-md border-b border-line">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <img
            src="/icon.png"
            alt="快拨通讯录"
            class="w-9 h-9 rounded-xl object-cover"
          />
          <span class="font-semibold text-lg">快拨通讯录</span>
        </div>

        <div class="flex items-center gap-3">
          <span class="hidden sm:inline text-sm text-muted">{{ user?.phone }}</span>
          <button
            type="button"
            class="flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm text-muted hover:bg-surface hover:text-ink transition-colors"
            @click="handleLogout"
          >
            <LogOut class="w-4 h-4" />
            退出
          </button>
        </div>
      </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
      <div class="flex items-end justify-between gap-4 mb-8">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold">拨号页</h1>
          <p class="mt-1 text-muted text-sm">创建并管理你的拨号通讯录</p>
        </div>
        <div class="flex items-center gap-3">
          <button
            type="button"
            class="flex items-center gap-2 rounded-xl border border-line px-4 py-2.5 text-sm font-semibold hover:bg-surface transition-colors"
            @click="showHowTo = true"
          >
            如何使用
          </button>
          <button
            type="button"
            class="flex items-center gap-2 rounded-xl bg-accent text-white px-4 py-2.5 text-sm font-semibold hover:bg-accent-dark transition-colors"
            @click="showCreate = true"
          >
            <Plus class="w-4 h-4" />
            新建拨号页
          </button>
        </div>
      </div>

      <div v-if="loading" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div
          v-for="i in 3"
          :key="i"
          class="h-44 rounded-2xl bg-surface/70 animate-pulse"
        ></div>
      </div>

      <div v-else-if="!pages.length" class="text-center py-24">
        <div class="mx-auto w-16 h-16 rounded-2xl bg-surface grid place-items-center shadow-card">
          <PhoneCall class="w-7 h-7 text-muted" />
        </div>
        <p class="mt-5 font-semibold">还没有拨号页</p>
        <p class="mt-1 text-sm text-muted">创建第一个拨号页，分享给需要联系你的人</p>
        <button
          type="button"
          class="mt-6 inline-flex items-center gap-2 rounded-xl bg-accent text-white px-4 py-2.5 text-sm font-semibold hover:bg-accent-dark transition-colors"
          @click="showCreate = true"
        >
          <Plus class="w-4 h-4" />
          立即创建
        </button>
      </div>

      <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div
          v-for="(page, i) in pages"
          :key="page.id"
          class="group flex flex-col rounded-2xl bg-surface shadow-card hover:shadow-pop transition-shadow animate-rise"
          :style="{ animationDelay: `${i * 60}ms` }"
        >
          <div class="flex-1 p-5">
            <div class="flex items-start justify-between">
              <h3 class="font-semibold text-lg leading-tight">{{ page.name }}</h3>
              <span
                class="flex items-center gap-1 text-xs text-muted bg-paper rounded-full px-2.5 py-1"
              >
                <Users class="w-3.5 h-3.5" />
                {{ page.contact_count ?? 0 }}
              </span>
            </div>
            <p class="mt-3 text-xs text-muted truncate">
              /d/{{ page.slug }}
            </p>
          </div>

          <div class="flex items-center gap-1.5 p-3 border-t border-line">
            <button
              type="button"
              class="flex-1 flex items-center justify-center gap-1.5 rounded-xl py-2 text-sm text-muted hover:bg-paper hover:text-ink transition-colors"
              @click="router.push(`/admin/page/${page.id}`)"
            >
              <Pencil class="w-4 h-4" />
              编辑
            </button>
            <button
              type="button"
              class="flex-1 flex items-center justify-center gap-1.5 rounded-xl py-2 text-sm text-muted hover:bg-paper hover:text-ink transition-colors"
              @click="shareTarget = page"
            >
              <Share2 class="w-4 h-4" />
              分享
            </button>
            <button
              type="button"
              class="w-10 h-9 grid place-items-center rounded-xl text-muted hover:bg-accent/10 hover:text-accent transition-colors"
              title="删除"
              @click="removePage(page)"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </main>

    <BaseModal
      v-if="showCreate"
      title="新建拨号页"
      @close="showCreate = false"
    >
      <div class="grid gap-4">
        <label class="grid gap-1.5">
          <span class="text-sm text-muted">拨号页名称</span>
          <input
            v-model="createName"
            type="text"
            placeholder="例如：公司通讯录"
            class="rounded-xl border border-line px-3 py-2.5 text-sm outline-none focus:border-accent focus:ring-2 focus:ring-accent/20 transition"
            @keyup.enter="createPage"
          />
        </label>
        <div class="flex gap-3">
          <button
            type="button"
            class="flex-1 rounded-xl border border-line py-2.5 text-sm font-medium hover:bg-paper transition-colors"
            @click="showCreate = false"
          >
            取消
          </button>
          <button
            type="button"
            class="flex-1 rounded-xl bg-accent text-white py-2.5 text-sm font-medium hover:bg-accent-dark transition-colors"
            @click="createPage"
          >
            创建
          </button>
        </div>
      </div>
    </BaseModal>

    <ShareModal v-if="shareTarget" :page="shareTarget" @close="shareTarget = null" />

    <HowToUseModal v-if="showHowTo" @close="showHowTo = false" />
  </div>
</template>
