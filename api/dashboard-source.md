# 💻 官方仪表盘源码 (Open Source Dashboard)

为了让全世界的开发者能够更直观地理解如何接入《标准猫历》(SFC) API，我们完全开源了官方实时控制台的 **Vue 3 单文件组件 (SFC)** 源码。

此组件内置了**物理网络延迟 (RTT) 测算**、**本地补帧倒计时**以及**实时进度条换算**逻辑。您可以直接复制以下代码，零配置集成到您自己的 Vue、VitePress 或 Nuxt 项目中。

## SfcDashboard.vue

```vue
<template>
  <div class="sfc-dashboard-wrapper">
    <div class="header">
      <div class="api-status" :class="{ 'error': hasError }">
        <span class="status-dot"></span>
        {{ apiStatusText }}
      </div>
    </div>

    <h3 v-if="loading" class="loading-text">正在同步全球边缘节点数据...</h3>

    <div v-else class="dashboard-grid">
      <div class="card">
        <div class="card-title"><span>🕒 时空坐标</span> <span class="node-name">{{ meta.node }}</span></div>
        <div class="time-display">
          <div class="feline-year">猫历 {{ feline.feline_year }} 年</div>
          <div class="feline-season">{{ feline.current_season.name_zh }}</div>
          <div class="feline-hour">{{ feline.current_hour.name_zh }}</div>
          <div class="earth-time-pill">
            <span class="earth-icon">🌍</span> 
            <span class="earth-time-text">{{ currentEarthTime }}</span>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-title"><span>📊 流逝进度</span></div>
        <div class="progress-group">
          <div class="progress-label"><span>猫时 ({{ feline.current_hour.name_zh }})</span> <span class="pct-num">{{ progress.hour_percentage }}%</span></div>
          <div class="progress-bar-bg"><div class="progress-bar-fill fill-hour" :style="{ width: progress.hour_percentage + '%' }"></div></div>
        </div>
        <div class="progress-group">
          <div class="progress-label"><span>猫季 ({{ feline.current_season.name_zh }})</span> <span class="pct-num">{{ progress.season_percentage }}%</span></div>
          <div class="progress-bar-bg"><div class="progress-bar-fill fill-season" :style="{ width: progress.season_percentage + '%' }"></div></div>
        </div>
        <div class="progress-group">
          <div class="progress-label"><span>本猫年</span> <span class="pct-num">{{ progress.feline_year_percentage }}%</span></div>
          <div class="progress-bar-bg"><div class="progress-bar-fill fill-year" :style="{ width: progress.feline_year_percentage + '%' }"></div></div>
        </div>
      </div>

      <div class="card">
        <div class="card-title"><span>🐈 行为指数</span></div>
        <div class="behavior-item"><span>狩猎欲</span> <span class="behavior-value">{{ behavior.hunting_drive }}</span></div>
        <div class="behavior-item"><span>睡眠欲</span> <span class="behavior-value">{{ behavior.sleepiness }}</span></div>
        <div class="behavior-item">
          <span>跑酷预警</span> 
          <span class="behavior-value" :class="{ danger: parseInt(behavior.zoomies_probability) >= 80 }">{{ behavior.zoomies_probability }}</span>
        </div>
        <div class="behavior-item"><span>掉毛率</span> <span class="behavior-value text-sm">{{ behavior.shedding_rate }}</span></div>
      </div>

      <div class="card">
        <div class="card-title"><span>⏳ 事件视界</span></div>
        <div class="countdown-box">
          <div class="countdown-title">距离下一个 [{{ nextEvents.next_feline_hour.name_zh }}]</div>
          <div class="countdown-timer">{{ formattedCdHour }}</div>
        </div>
        <div class="countdown-box">
          <div class="countdown-title">距离 [{{ nextEvents.next_feline_season.name_zh }}] 降临</div>
          <div class="countdown-timer">{{ formattedCdSeason }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'

// 💡 开发者提示：请将此处替换为您自己的业务节点
const API_URL = '[https://global.sfc-time.encore.baby](https://global.sfc-time.encore.baby)'

const loading = ref(true)
const hasError = ref(false)
const apiStatusText = ref('正在连接神经漫游者...')

const meta = ref({})
const feline = ref({ current_season: {}, current_hour: {} })
const progress = ref({})
const behavior = ref({})
const nextEvents = ref({ next_feline_hour: {}, next_feline_season: {} })

const currentEarthTime = ref('--')
const cdHourSecs = ref(0)
const cdSeasonSecs = ref(0)
let timer = null
let syncTimer = null

const formatSecs = (sec) => {
  const h = Math.floor(sec / 3600).toString().padStart(2, '0')
  const m = Math.floor((sec % 3600) / 60).toString().padStart(2, '0')
  const s = Math.floor(sec % 60).toString().padStart(2, '0')
  return `${h}:${m}:${s}`
}

const formattedCdHour = computed(() => formatSecs(cdHourSecs.value))
const formattedCdSeason = computed(() => formatSecs(cdSeasonSecs.value))

const fetchData = async () => {
  try {
    const requestStartTime = performance.now()
    const res = await fetch(API_URL)
    if (!res.ok) throw new Error('Network response was not ok')
    const data = await res.json()
    const requestEndTime = performance.now()
    
    // 精确计算 RTT 网络延迟
    const networkLatencyMs = Math.round(requestEndTime - requestStartTime)
    
    meta.value = data.meta
    feline.value = data.feline_time
    progress.value = data.progress
    behavior.value = data.behavior_index
    nextEvents.value = data.next_events
    cdHourSecs.value = data.next_events.next_feline_hour.countdown_seconds
    cdSeasonSecs.value = data.next_events.next_feline_season.countdown_seconds
    
    apiStatusText.value = `网络延迟 ${networkLatencyMs}ms | 节点执行 ${data.meta.server_processing_time_ms}ms | 时区: ${data.meta.client_timezone}`
    hasError.value = false
    loading.value = false
  } catch (err) {
    console.error(err)
    apiStatusText.value = 'API 连接断开，请检查网络'
    hasError.value = true
  }
}

onMounted(() => {
  fetchData()
  timer = setInterval(() => {
    if (!loading.value) {
      cdHourSecs.value = Math.max(0, cdHourSecs.value - 1)
      cdSeasonSecs.value = Math.max(0, cdSeasonSecs.value - 1)
      const now = new Date();
      const timeString = now.toLocaleTimeString('zh-CN', { hour12: false });
      const dateString = `${now.getFullYear()}/${(now.getMonth()+1).toString().padStart(2,'0')}/${now.getDate().toString().padStart(2,'0')}`;
      currentEarthTime.value = `${dateString} ${timeString}`;
      
      if (cdHourSecs.value === 0) fetchData()
    }
  }, 1000)
  syncTimer = setInterval(fetchData, 30000)
})

onUnmounted(() => {
  clearInterval(timer)
  clearInterval(syncTimer)
})
</script>

<style scoped>
.sfc-dashboard-wrapper {
  --card-bg: var(--vp-c-bg-soft);
  --text-muted: var(--vp-c-text-2);
  --accent-primary: #3b82f6;
  --accent-warning: #f59e0b;
  --accent-danger: #ef4444;
  --accent-success: #10b981;
  font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  margin-top: 2rem;
}

.header { text-align: center; margin-bottom: 2rem; }

.api-status { 
  display: inline-flex; align-items: center; gap: 6px;
  padding: 0.4rem 1rem; border-radius: 999px; font-size: 0.8rem; 
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  background: rgba(16, 185, 129, 0.1); color: var(--accent-success); 
  border: 1px solid rgba(16, 185, 129, 0.2);
}

.status-dot {
  width: 8px; height: 8px; background-color: var(--accent-success);
  border-radius: 50%; box-shadow: 0 0 8px var(--accent-success);
  animation: breathe 2s infinite ease-in-out;
}

.api-status.error { background: rgba(239, 68, 68, 0.1); color: var(--accent-danger); border-color: rgba(239, 68, 68, 0.2); }
.api-status.error .status-dot { background-color: var(--accent-danger); box-shadow: 0 0 8px var(--accent-danger); }

.loading-text { text-align: center; color: var(--text-muted); }
.dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; }
.card { background: var(--card-bg); border-radius: 12px; padding: 1.5rem; border: 1px solid var(--vp-c-divider); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
.card-title { font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 1px; display: flex; justify-content: space-between; align-items: center; }
.node-name { font-size: 0.75rem; background: var(--vp-c-default-soft); padding: 2px 6px; border-radius: 4px; font-family: monospace; }
.time-display { display: flex; flex-direction: column; align-items: center; gap: 0.8rem; padding: 0.5rem 0; }
.feline-year { font-size: 1.1rem; color: var(--text-muted); letter-spacing: 3px; }
.feline-season { font-size: 3.2rem; font-weight: 900; line-height: 1; color: var(--accent-warning); text-shadow: 0 4px 15px rgba(245, 158, 11, 0.25); letter-spacing: 2px; }
.feline-hour { font-size: 1.8rem; font-weight: 700; color: var(--accent-primary); letter-spacing: 4px; }
.earth-time-pill { margin-top: 0.8rem; background: var(--vp-c-default-soft); padding: 0.4rem 1rem; border-radius: 20px; display: flex; align-items: center; gap: 8px; border: 1px solid var(--vp-c-divider); }
.earth-time-text { font-size: 0.85rem; color: var(--vp-c-text-1); font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
.progress-group { margin-bottom: 1.2rem; }
.progress-label { display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.85rem; color: var(--vp-c-text-1); }
.pct-num { font-family: ui-monospace, monospace; font-size: 0.8rem; color: var(--text-muted); }
.progress-bar-bg { width: 100%; height: 6px; background: var(--vp-c-default-soft); border-radius: 4px; overflow: hidden; }
.progress-bar-fill { height: 100%; border-radius: 4px; transition: width 0.5s ease-out; }
.fill-year { background: var(--text-muted); }
.fill-season { background: var(--accent-warning); }
.fill-hour { background: var(--accent-primary); }
.behavior-item { display: flex; justify-content: space-between; align-items: center; padding: 0.7rem 0; border-bottom: 1px solid var(--vp-c-divider); font-size: 0.95rem; }
.behavior-item:last-child { border-bottom: none; }
.behavior-value { font-weight: bold; font-size: 1.1rem; font-family: ui-monospace, monospace; }
.text-sm { font-size: 0.9rem; }
.danger { color: var(--accent-danger); animation: danger-pulse 1.5s infinite; text-shadow: 0 0 8px rgba(239, 68, 68, 0.4); }
.countdown-box { background: var(--vp-c-default-soft); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; text-align: center; border: 1px solid rgba(255,255,255,0.02); }
.countdown-title { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem; }
.countdown-timer { font-size: 1.6rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-weight: bold; color: var(--vp-c-text-1); letter-spacing: 2px; }

@keyframes breathe { 0% { opacity: 0.4; transform: scale(0.9); } 50% { opacity: 1; transform: scale(1.1); } 100% { opacity: 0.4; transform: scale(0.9); } }
@keyframes danger-pulse { 0% { opacity: 1; transform: scale(1); } 50% { opacity: 0.8; color: #ff0000; transform: scale(1.05); } 100% { opacity: 1; transform: scale(1); } }
</style>
```