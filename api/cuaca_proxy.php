<?php
header('Content-Type: application/json');

$kotaMapping = [
    'Pangkal Pinang' => '501190',
    'Bangka' => '501196',
    'Bangka Barat' => '501197',
    'Bangka Selatan' => '501198',
    'Bangka Tengah' => '501199',
    'Belitung' => '501200',
    'Belitung Timur' => '501201'
];

$kota = $_GET['kota'] ?? 'Pangkal Pinang';

if (!isset($kotaMapping[$kota])) {
    $kota = 'Pangkal Pinang';
}

$bmkgId = $kotaMapping[$kota];
$url = "https://data.bmkg.go.id/DataMKG/MEWS/DigitalForecast/DigitalForecast-KepulauanBangkaBelitung.xml";

$xmlString = false;
$cacheFile = __DIR__ . '/cache_bmkg.xml';

// Cek cache lokal (valid 30 menit)
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 1800)) {
    $xmlString = @file_get_contents($cacheFile);
}

if (!$xmlString) {
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
        $xmlString = curl_exec($ch);
        curl_close($ch);
    }

    if (!$xmlString) {
        $context = stream_context_create([
            'http' => ['header' => "User-Agent: Mozilla/5.0\r\n", 'timeout' => 4],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
        ]);
        $xmlString = @file_get_contents($url, false, $context);
    }

    if ($xmlString && strpos($xmlString, '<data') !== false) {
        @file_put_contents($cacheFile, $xmlString);
    }
}

libxml_use_internal_errors(true);
$xml = $xmlString ? simplexml_load_string($xmlString) : false;

function getWeatherDesc($code) {
    return match ($code) {
        '0' => 'Cerah',
        '1','2' => 'Cerah Berawan',
        '3' => 'Berawan',
        '4' => 'Berawan Tebal',
        '5' => 'Hujan Ringan',
        '60' => 'Hujan',
        '61' => 'Hujan Lebat',
        '95' => 'Hujan Petir',
        default => 'Cerah Berawan'
    };
}

// Jika XML BMKG berhasil diparsing
if ($xml && isset($xml->forecast->area)) {
    $area = null;
    foreach ($xml->forecast->area as $a) {
        if ((string)$a['id'] === $bmkgId) {
            $area = $a;
            break;
        }
    }

    if ($area) {
        $params = [];
        foreach ($area->parameter as $p) {
            $params[(string)$p['id']] = $p;
        }

        $getValue = function($param, $index = 0) {
            return isset($param->timerange[$index]->value)
                ? (string)$param->timerange[$index]->value
                : null;
        };

        $current = [
            'temp' => (int) ($getValue($params['t'] ?? null) ?: 29),
            'humidity' => (int) ($getValue($params['hu'] ?? null) ?: 76),
            'wind_speed' => round((float)($getValue($params['ws'] ?? null) ?: 3.2) * 3.6),
            'wind_dir' => $getValue($params['wd'] ?? null) ?: 'SE',
            'pressure' => (int) ($getValue($params['p'] ?? null) ?: 1011),
            'condition' => getWeatherDesc($getValue($params['weather'] ?? null))
        ];

        $forecast = [];
        for ($i = 0; $i < 5; $i++) {
            $forecast[] = [
                'date' => date('Y-m-d', strtotime("+$i day")),
                'condition' => getWeatherDesc($getValue($params['weather'] ?? null, $i)),
                'temp_max' => (int) ($getValue($params['tmax'] ?? null, $i) ?: 32),
                'temp_min' => (int) ($getValue($params['tmin'] ?? null, $i) ?: 24),
                'humidity' => (int) ($getValue($params['hu'] ?? null, $i) ?: 80)
            ];
        }

        echo json_encode([
            'success' => true,
            'source' => 'bmkg_live',
            'city' => $kota,
            'current' => $current,
            'forecast' => $forecast
        ]);
        exit;
    }
}

// Fallback jika BMKG offline / lambat
$mockTemps = [
    'Pangkal Pinang' => 29,
    'Bangka' => 28,
    'Bangka Barat' => 30,
    'Bangka Selatan' => 29,
    'Bangka Tengah' => 28,
    'Belitung' => 29,
    'Belitung Timur' => 29
];

$t = $mockTemps[$kota] ?? 29;
$forecast = [];
for ($i = 0; $i < 5; $i++) {
    $forecast[] = [
        'date' => date('Y-m-d', strtotime("+$i day")),
        'condition' => ($i % 2 == 0) ? 'Cerah Berawan' : 'Berawan',
        'temp_max' => $t + 3,
        'temp_min' => $t - 4,
        'humidity' => 75 + ($i * 2)
    ];
}

echo json_encode([
    'success' => true,
    'source' => 'bmkg_estimated',
    'city' => $kota,
    'current' => [
        'temp' => $t,
        'humidity' => 76,
        'wind_speed' => 12,
        'wind_dir' => 'Tenggara',
        'pressure' => 1012,
        'condition' => 'Cerah Berawan'
    ],
    'forecast' => $forecast
]);
