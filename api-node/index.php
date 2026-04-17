<?php
/**
 * Standard Feline Calendar (SFC) - China Node
 * Version: 1.1.0-detailed
 * Baseline: 1 Earth Year = 5 Feline Years (73 Days/Feline Year)
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$server_start_time = microtime(true);

$client_timestamp = isset($_GET['timestamp']) ? (float)$_GET['timestamp'] : $server_start_time;
$tz = new DateTimeZone('Asia/Shanghai');

try {

    $dt = DateTime::createFromFormat('U.u', sprintf('%.6F', $client_timestamp));
    if ($dt === false) {
        $dt = new DateTime('@' . (int)$client_timestamp);
    }
    $dt->setTimezone($tz);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid timestamp format."]);
    exit;
}

$earth_year = (int)$dt->format('Y');
$day_of_year = (int)$dt->format('z');
$earth_hour = (int)$dt->format('G');
$earth_min = (int)$dt->format('i');
$earth_sec = (int)$dt->format('s');
$current_ts = $dt->getTimestamp();

$feline_year = ($earth_year + 7500) * 5;

$season_index = $day_of_year % 73;
$day_of_feline_year = $season_index + 1;

$days_in_current_season = 0;
$total_days_in_season = 18;

if ($season_index <= 18) {
    $season = ["name_zh" => "生发季", "name_en" => "Sprouting", "desc" => "掉毛季，极其躁动"];
    $days_in_current_season = $season_index;
    $total_days_in_season = 19;
} elseif ($season_index <= 36) {
    $season = ["name_zh" => "融化季", "name_en" => "Melting", "desc" => "高温预警，猫咪呈液体状"];
    $days_in_current_season = $season_index - 19;
} elseif ($season_index <= 54) {
    $season = ["name_zh" => "敛藏季", "name_en" => "Hoarding", "desc" => "食欲暴涨，为过冬贴秋膘"];
    $days_in_current_season = $season_index - 37;
} else {
    $season = ["name_zh" => "封印季", "name_en" => "Sealing", "desc" => "物理封印在暖气或被窝中"];
    $days_in_current_season = $season_index - 55;
}

$hour_index = floor($earth_hour / 4);
$hours_map = [
    ["name_zh" => "伏击时", "range" => "00:00 - 04:00", "desc" => "浅眠与暗中视察"],
    ["name_zh" => "晨猎时", "range" => "04:00 - 08:00", "desc" => "精力巅峰，跑酷与索要猫粮"],
    ["name_zh" => "巡窗时", "range" => "08:00 - 12:00", "desc" => "领地视察与阳光浴"],
    ["name_zh" => "大梦时", "range" => "12:00 - 16:00", "desc" => "深度睡眠，勿扰"],
    ["name_zh" => "黄昏猎", "range" => "16:00 - 20:00", "desc" => "次级活跃期，宜使用逗猫棒"],
    ["name_zh" => "理毛时", "range" => "20:00 - 00:00", "desc" => "梳洗打扮与社交相伴"]
];

$year_pct = round(($day_of_feline_year / 73) * 100, 2);
$season_pct = round(($days_in_current_season / $total_days_in_season) * 100, 2);

$mins_in_hour = ($earth_hour % 4) * 60 + $earth_min + ($earth_sec / 60);
$hour_pct = round(($mins_in_hour / 240) * 100, 2);

$dt_next_hour = clone $dt;
$next_hour_trigger = ($hour_index + 1) * 4;
if ($next_hour_trigger >= 24) {
    $dt_next_hour->modify('+1 day');
    $next_hour_trigger = 0;
}
$dt_next_hour->setTime($next_hour_trigger, 0, 0);
$cd_hour = $dt_next_hour->getTimestamp() - $current_ts;

$dt_next_season = clone $dt;
$days_to_next = $total_days_in_season - $days_in_current_season;
$dt_next_season->modify('+' . $days_to_next . ' days');
$dt_next_season->setTime(0, 0, 0);
$cd_season = $dt_next_season->getTimestamp() - $current_ts;

$seasons_zh = ["生发季", "融化季", "敛藏季", "封印季"];
$next_season_name = $seasons_zh[(array_search($season['name_zh'], $seasons_zh) + 1) % 4];

$profiles = [
    0 => ["h" => "30%", "s" => "60%", "z" => "40%"],
    1 => ["h" => "95%", "s" => "5%",  "z" => "100%"],
    2 => ["h" => "40%", "s" => "50%", "z" => "20%"],
    3 => ["h" => "10%", "s" => "99%", "z" => "0%"],
    4 => ["h" => "85%", "s" => "15%", "z" => "80%"],
    5 => ["h" => "20%", "s" => "70%", "z" => "10%"]
];
$behavior = $profiles[$hour_index];
$shedding = ($season['name_zh'] == "生发季") ? "99% (严重)" : (($season['name_zh'] == "封印季") ? "10% (极少)" : "40% (普通)");

$server_end_time = microtime(true);
$processing_ms = round(($server_end_time - $server_start_time) * 1000, 4);

$response = [
    "status" => "success",
    "meta" => [
        "node" => "cn-private-server",
        "api_version" => "1.1.0-detailed",
        "client_timezone" => $dt->getTimezone()->getName(),
        "server_processing_time_ms" => $processing_ms,
        "server_exact_timestamp_ms" => round($server_end_time * 1000)
    ],
    "earth_time" => [
        "iso" => $dt->format('c'),
        "unix_timestamp" => $current_ts
    ],
    "feline_time" => [
        "feline_year" => $feline_year,
        "day_of_feline_year" => $day_of_feline_year,
        "current_season" => $season,
        "current_hour" => $hours_map[$hour_index]
    ],
    "progress" => [
        "feline_year_percentage" => $year_pct,
        "season_percentage" => $season_pct,
        "hour_percentage" => $hour_pct
    ],
    "next_events" => [
        "next_feline_hour" => [
            "name_zh" => $hours_map[($hour_index + 1) % 6]['name_zh'],
            "earth_time_iso" => $dt_next_hour->format('c'),
            "countdown_seconds" => $cd_hour
        ],
        "next_feline_season" => [
            "name_zh" => $next_season_name,
            "earth_time_iso" => $dt_next_season->format('c'),
            "countdown_seconds" => $cd_season
        ]
    ],
    "behavior_index" => [
        "hunting_drive" => $behavior["h"],
        "sleepiness" => $behavior["s"],
        "zoomies_probability" => $behavior["z"],
        "shedding_rate" => $shedding
    ]
];

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);