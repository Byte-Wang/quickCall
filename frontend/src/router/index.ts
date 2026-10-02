import { createRouter, createWebHashHistory, type RouteRecordRaw } from 'vue-router'
import { getToken } from '@/lib/http'
import LoginPage from '@/pages/LoginPage.vue'
import RegisterPage from '@/pages/RegisterPage.vue'
import DashboardPage from '@/pages/DashboardPage.vue'
import DialPageEditorPage from '@/pages/DialPageEditorPage.vue'
import DialViewPage from '@/pages/DialViewPage.vue'

const routes: RouteRecordRaw[] = [
  { path: '/', redirect: '/admin' },
  {
    path: '/login',
    name: 'login',
    component: LoginPage,
    meta: { guest: true },
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterPage,
    meta: { guest: true },
  },
  {
    path: '/admin',
    name: 'dashboard',
    component: DashboardPage,
    meta: { auth: true },
  },
  {
    path: '/admin/page/:id',
    name: 'editor',
    component: DialPageEditorPage,
    meta: { auth: true },
  },
  {
    path: '/d/:slug',
    name: 'dial',
    component: DialViewPage,
  },
  {
    path: '/d/:slug/:contactId',
    name: 'dial-contact',
    component: DialViewPage,
  },
]

const router = createRouter({
  history: createWebHashHistory(),
  routes,
})

router.beforeEach((to) => {
  const token = getToken()

  if (to.meta.auth && !token) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.guest && token) {
    return { name: 'dashboard' }
  }
})

// 仅登录后的管理页面挂载 vConsole，用于在旧设备上排查白屏等问题。
// 拨号页（/d/:slug）与登录页、注册页均不显示。
let vConsole: { destroy: () => void } | null = null
let shouldMount = false

async function mountVConsole() {
  shouldMount = true
  if (vConsole) return
  const { default: VConsole } = await import('vconsole')
  // 异步加载期间若已离开管理页，则不再挂载
  if (!shouldMount) return
  vConsole = new VConsole()
}

function unmountVConsole() {
  shouldMount = false
  if (vConsole) {
    vConsole.destroy()
    vConsole = null
  }
}

router.afterEach((to) => {
  if (to.meta.auth) {
    mountVConsole()
  } else {
    unmountVConsole()
  }
})

export default router
