# 🚀 API 接入指南与参考文档

SFC 提供了高可用、带地理位置感知（时区自适应）的 RESTful API。无论您是开发智能喂食器、桌面日历还是天气应用，都可以轻松接入。

## 🌍 节点选择

为了保证授时的毫秒级精准度，请根据您的业务场景选择最优节点：

* **全球边缘节点 (Global):** `https://global.sfc-time.encore.baby` (Cloudflare 强力驱动，毫秒级响应海外请求，自动识别时区)
* **中国大陆节点 (China):** `https://china.sfc-time.encore.baby` (专属优化线路，固定东八区基准，适合国内纯内网/本地化智能家居)

## 📡 请求方式

* **Method:** `GET`
* **参数 (Params):**
    * `timestamp` *(可选)*: Unix 时间戳（秒或微秒）。如果不传，则返回服务器当前时间。用于“时光机”预测历史或未来猫历。

```bash
# 获取当前猫历
curl [https://global.sfc-time.encore.baby/](https://global.sfc-time.encore.baby/)

# 预测未来 (如 2030年的某一天)
curl [https://global.sfc-time.encore.baby/?timestamp=1893456000](https://global.sfc-time.encore.baby/?timestamp=1893456000)
```

---

## 📦 响应数据结构 (JSON)

SFC API 采用极严谨的结构化设计，不仅包含基础时间，还原生提供**进度条换算**、**倒计时预测**和**行为学指数**。

### 完整响应示例

```json
{
  "status": "success",
  "meta": {
    "node": "cf-edge-global",
    "api_version": "1.1.0-detailed",
    "client_timezone": "Asia/Shanghai",
    "server_processing_time_ms": 2.15
  },
  "earth_time": {
    "iso": "2026-04-17T20:51:11.000Z",
    "timezone": "Asia/Shanghai (Detected)",
    "unix_timestamp": 1776430271
  },
  "feline_time": {
    "feline_year": 47630,
    "day_of_feline_year": 34,
    "current_season": {
      "name_zh": "融化季",
      "name_en": "Melting",
      "desc": "高温预警，猫咪呈液体状"
    },
    "current_hour": {
      "name_zh": "理毛时",
      "range": "20:00 - 00:00",
      "desc": "梳洗打扮与社交相伴"
    }
  },
  "progress": {
    "feline_year_percentage": 46.58,
    "season_percentage": 88.89,
    "hour_percentage": 21.32
  },
  "next_events": {
    "next_feline_hour": {
      "name_zh": "伏击时",
      "earth_time_iso": "2026-04-18T00:00:00.000Z",
      "countdown_seconds": 11329
    },
    "next_feline_season": {
      "name_zh": "敛藏季",
      "earth_time_iso": "2026-04-20T00:00:00.000Z",
      "countdown_seconds": 184129
    }
  },
  "behavior_index": {
    "hunting_drive": "20%",
    "sleepiness": "70%",
    "zoomies_probability": "10%",
    "shedding_rate": "40% (普通)"
  }
}
```

---

## 🗂️ 响应字段字典 (Data Dictionary)

API 返回的 JSON 数据由六大核心模块构成。以下是每个字段的严格定义与适用场景：

### 1. 基础状态 (Root)
| 字段名 | 类型 | 说明 |
| :--- | :--- | :--- |
| `status` | String | 请求状态。正常返回 `"success"`。 |

### 2. 元数据 (`meta`)
包含请求的上下文与服务器性能指标，常用于网络延迟补偿计算。
| 字段名 | 类型 | 说明 | 示例 |
| :--- | :--- | :--- | :--- |
| `node` | String | 响应此请求的服务器节点标识。 | `"cf-edge-global"` 或 `"cn-private-server"` |
| `api_version` | String | 当前 API 的版本号。 | `"1.1.0-detailed"` |
| `client_timezone` | String | 服务器识别到的客户端时区（用于支撑地理感知）。 | `"Asia/Shanghai"` |
| `server_processing_time_ms` | Float | 后端核心计算的耗时（毫秒），体现接口性能。 | `1.24` |
| `server_exact_timestamp_ms` | Integer | 服务器处理完成瞬间的精确毫秒级时间戳。**强烈建议前端使用此字段结合 RTT 进行网络延迟校准。** | `1776430271123` |

### 3. 地球时间基准 (`earth_time`)
猫历的换算锚点，方便开发者与人类世界的时间系统对齐。
| 字段名 | 类型 | 说明 | 示例 |
| :--- | :--- | :--- | :--- |
| `iso` | String | 符合 ISO 8601 标准的当前地球时间。 | `"2026-04-17T20:51:11.000Z"` |
| `timezone` | String | 计算所基于的时区。 | `"Asia/Shanghai (Detected)"` |
| `unix_timestamp` | Integer | 当前的 Unix 秒级时间戳。 | `1776430271` |

### 4. 猫历核心 (`feline_time`)
标准猫历 (SFC) 的主要时间刻度。
| 字段名 | 类型 | 说明 | 示例 |
| :--- | :--- | :--- | :--- |
| `feline_year` | Integer | 当前的猫历年份（1地球年=5猫年）。 | `47630` |
| `day_of_feline_year` | Integer | 当前是本猫历年中的第几天 (范围 1-73)。 | `34` |
| `current_season` | Object | **当前猫季**。包含 `name_zh`(中文名), `name_en`(英文名), `desc`(描述)。 | `{"name_zh": "融化季", ...}` |
| `current_hour` | Object | **当前猫时**。包含 `name_zh`(如"大梦时"), `range`(对应地球时段), `desc`(描述)。 | `{"name_zh": "大梦时", ...}` |

### 5. 进度条系统 (`progress`)
**前端 UI 绘图专属**。直接输出百分比，无需前端二次计算，完美适配环形图或线性进度条。
| 字段名 | 类型 | 说明 | 示例 |
| :--- | :--- | :--- | :--- |
| `feline_year_percentage` | Float | 当前猫年已经过去的百分比 (0.00 - 100.00)。 | `46.58` |
| `season_percentage` | Float | 当前“猫季”已经度过的百分比。 | `88.89` |
| `hour_percentage` | Float | 当前“猫时”已经度过的百分比。 | `21.32` |

### 6. 事件预测 (`next_events`)
**定时任务、倒计时组件专属**。提供下一个重要猫历节点的精确降临时间。
| 字段名 | 类型 | 说明 |
| :--- | :--- | :--- |
| `next_feline_hour` | Object | **下一个猫时**。包含名称、绝对 ISO 时间，以及 `countdown_seconds` (距离下一个猫时降临的秒数)。 |
| `next_feline_season` | Object | **下一个猫季**。同上，包含季节更替的绝对时间与倒计时秒数。 |

### 7. 行为学指数 (`behavior_index`)
根据时间与季节动态推算的猫咪状态概率，适合开发“猫咪状态预报”功能。
| 字段名 | 类型 | 说明 | 示例 |
| :--- | :--- | :--- | :--- |
| `hunting_drive` | String | 狩猎欲。决定猫咪对逗猫棒的反应程度。 | `"85%"` |
| `sleepiness` | String | 睡眠欲。数值过高时建议人类保持安静。 | `"99%"` |
| `zoomies_probability` | String | **跑酷概率**。高能预警指标，达到 80% 以上时请收起易碎品。 | `"100%"` |
| `shedding_rate` | String | 掉毛率。由季节决定，指导人类扫地机器人的出勤频率。 | `"99% (严重)"` |
```