/** @type {import('tailwindcss').Config} */

export default {
  darkMode: 'class',
  content: ['./index.html', './src/**/*.{js,ts,vue}'],
  theme: {
    container: {
      center: true,
    },
    extend: {
      colors: {
        paper: 'var(--c-paper)',
        surface: 'var(--c-surface)',
        ink: 'var(--c-ink)',
        muted: 'var(--c-muted)',
        line: 'var(--c-line)',
        accent: 'var(--c-accent)',
        'accent-dark': 'var(--c-accent-dark)',
      },
      fontFamily: {
        sans: [
          'Sora',
          'PingFang SC',
          'Noto Sans SC',
          'Microsoft YaHei',
          'system-ui',
          'sans-serif',
        ],
      },
      boxShadow: {
        card: '0 1px 2px rgba(32, 26, 23, 0.04), 0 8px 24px -12px rgba(32, 26, 23, 0.18)',
        pop: '0 12px 40px -12px rgba(32, 26, 23, 0.32)',
      },
    },
  },
  plugins: [],
};
