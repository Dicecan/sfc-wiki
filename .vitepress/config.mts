import { defineConfig } from 'vitepress'

export default defineConfig({
  head: [['link', { rel: 'icon', href: '/favicon.ico' }]],

  locales: {
    root: {
      label: '简体中文',
      lang: 'zh-CN',
      title: "标准猫历 SFC",
      themeConfig: {
        nav: [
          { text: '首页', link: '/' },
          { text: '实时面板', link: '/dashboard' },
          { text: '指南', link: '/guide/fundamentals' },
          { text: 'API 接入', link: '/api/endpoints' }
        ],
        sidebar: [
          {
            text: '📺 监控室',
            items: [
              { text: '实时控制台', link: '/dashboard' }
            ]
          },
          {
            text: '📖 基础设定',
            items: [
              { text: '核心体系', link: '/guide/fundamentals' }
            ]
          },
          {
            text: '💻 开发者',
            items: [
              { text: 'API 接口文档', link: '/api/endpoints' },
              { text: '组件源码', link: '/api/dashboard-source' }
            ]
          }
        ]
      }
    },
    en: {
      label: 'English',
      lang: 'en-US',
      link: '/en/',
      themeConfig: {
        sidebar: [
          {
            text: '📺 Monitoring',
            items: [
              { text: 'Live Dashboard', link: '/dashboard' }
            ]
          },
          { text: '📖 Fundamentals', items: [{ text: 'Core System', link: '/en/guide/fundamentals' }] },
          { text: '💻 Developers', items: [
            { text: 'API Reference', link: '/en/api/endpoints' },
            { text: 'Source Code', link: '/en/api/dashboard-source' }
          ]}
        ]
      }
    }
  }
})