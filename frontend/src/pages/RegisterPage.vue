<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { toast } from '@/composables/useToast'
import { Lock, Phone, PhoneCall } from 'lucide-vue-next'

const router = useRouter()
const { register } = useAuth()

const phone = ref('')
const password = ref('')
const confirm = ref('')
const loading = ref(false)

async function submit() {
  if (!/^1\d{10}$/.test(phone.value)) {
    toast('请输入正确的手机号', 'error')
    return
  }
  if (password.value.length < 6) {
    toast('密码至少 6 位', 'error')
    return
  }
  if (password.value !== confirm.value) {
    toast('两次输入的密码不一致', 'error')
    return
  }

  loading.value = true
  try {
    await register(phone.value, password.value)
    toast('注册成功', 'success')
    router.replace('/admin')
  } catch (e) {
    toast((e as Error).message, 'error')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen grid lg:grid-cols-2">
    <div
      class="relative hidden lg:flex flex-col justify-between p-12 bg-ink text-white overflow-hidden"
    >
      <div class="relative z-10 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-accent grid place-items-center">
          <PhoneCall class="w-5 h-5" />
        </div>
        <span class="font-semibold text-lg">快拨通讯录</span>
      </div>

      <div class="relative z-10 max-w-md">
        <h1 class="text-4xl font-bold leading-tight">几分钟创建<br />你的拨号页</h1>
        <p class="mt-4 text-white/60 text-lg">
          用手机号注册即可开始，自动登录，无需验证码。
        </p>
      </div>

      <div class="relative z-10 text-sm text-white/40">注册成功即自动登录</div>

      <div
        class="absolute -right-24 -top-24 w-96 h-96 rounded-full bg-accent/25 blur-3xl"
      ></div>
      <div class="absolute -left-24 bottom-0 w-72 h-72 rounded-full bg-white/5"></div>
    </div>

    <div class="flex items-center justify-center p-6 sm:p-10">
      <div class="w-full max-w-sm animate-rise">
        <div class="lg:hidden flex items-center gap-2 mb-8">
          <div class="w-9 h-9 rounded-xl bg-accent grid place-items-center text-white">
            <PhoneCall class="w-5 h-5" />
          </div>
          <span class="font-semibold text-lg">快拨通讯录</span>
        </div>

        <h2 class="text-2xl font-bold">创建账号</h2>
        <p class="mt-1 text-muted text-sm">只需手机号和密码</p>

        <form class="mt-8 grid gap-4" @submit.prevent="submit">
          <label class="grid gap-1.5">
            <span class="text-sm text-muted">手机号</span>
            <div class="relative">
              <Phone
                class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-muted"
              />
              <input
                v-model="phone"
                type="tel"
                maxlength="11"
                placeholder="请输入手机号"
                class="w-full rounded-xl border border-line bg-surface pl-10 pr-3 py-3 text-sm outline-none focus:border-accent focus:ring-2 focus:ring-accent/20 transition"
              />
            </div>
          </label>

          <label class="grid gap-1.5">
            <span class="text-sm text-muted">密码</span>
            <div class="relative">
              <Lock
                class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-muted"
              />
              <input
                v-model="password"
                type="password"
                placeholder="至少 6 位"
                class="w-full rounded-xl border border-line bg-surface pl-10 pr-3 py-3 text-sm outline-none focus:border-accent focus:ring-2 focus:ring-accent/20 transition"
              />
            </div>
          </label>

          <label class="grid gap-1.5">
            <span class="text-sm text-muted">重复密码</span>
            <div class="relative">
              <Lock
                class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-muted"
              />
              <input
                v-model="confirm"
                type="password"
                placeholder="再次输入密码"
                class="w-full rounded-xl border border-line bg-surface pl-10 pr-3 py-3 text-sm outline-none focus:border-accent focus:ring-2 focus:ring-accent/20 transition"
              />
            </div>
          </label>

          <button
            type="submit"
            :disabled="loading"
            class="mt-2 rounded-xl bg-accent text-white py-3 text-sm font-semibold hover:bg-accent-dark transition-colors disabled:opacity-60"
          >
            {{ loading ? '注册中…' : '注册' }}
          </button>
        </form>

        <p class="mt-6 text-center text-sm text-muted">
          已有账号？
          <router-link to="/login" class="text-accent font-medium hover:underline">
            去登录
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>
