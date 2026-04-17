import DefaultTheme from 'vitepress/theme'
import SfcDashboard from './components/SfcDashboard.vue'

export default {
  ...DefaultTheme,
  enhanceApp({ app }) {
    app.component('SfcDashboard', SfcDashboard)
  }
}