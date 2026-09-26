import { defineConfig, type Plugin } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'
import Inspector from 'unplugin-vue-dev-locator/vite'

// 构建时向 index.html 注入第三方统计脚本
function injectAnalytics(): Plugin {
  return {
    name: 'inject-analytics',
    apply: 'build',
    transformIndexHtml() {
      return [
        {
          tag: 'script',
          injectTo: 'head',
          children: `var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "https://hm.baidu.com/hm.js?29f778d19e8a04dc12b699f386d398c8";
  var s = document.getElementsByTagName("script")[0];
  s.parentNode.insertBefore(hm, s);
})();`,
        },
        {
          tag: 'script',
          injectTo: 'head',
          children: `(function () {
  var s = document.createElement('script');
  s.async = true;
  s.src = 'https://tongji.wangdalong.top/backend/loader.php?code=106ee5e17cd52a39';
  var f = document.getElementsByTagName('script')[0];
  f.parentNode.insertBefore(s, f);
})();`,
        },
      ]
    },
  }
}

// https://vite.dev/config/
export default defineConfig({
  build: {
    sourcemap: 'hidden',
  },
  plugins: [vue(), Inspector(), injectAnalytics()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    proxy: {
      '/api': 'http://localhost:8000',
      '/uploads': 'http://localhost:8000',
    },
  },
})
