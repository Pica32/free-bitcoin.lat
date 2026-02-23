<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://free-bitcoin.lat');
header('Cache-Control: public, max-age=900');

define('CACHE_DIR', __DIR__ . '/cache/');
define('PREMIUM_TTL', 900); // 15 minutes

function curl_get(string $url): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 12,
        CURLOPT_CONNECTTIMEOUT => 6,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'free-bitcoin.lat/1.0',
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $body = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$body, $code];
}

function read_cache(string $file, int $ttl): ?array {
    if (!file_exists($file)) return null;
    if ((time() - filemtime($file)) > $ttl) return null;
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : null;
}

function write_cache(string $file, array $data): void {
    if (!is_dir(CACHE_DIR)) mkdir(CACHE_DIR, 0755, true);
    file_put_contents($file, json_encode($data), LOCK_EX);
}

// --- Argentina premium ---
function get_argentina_premium(): array {
    $cacheFile = CACHE_DIR . 'ar_premium.json';
    $cached = read_cache($cacheFile, PREMIUM_TTL);
    if ($cached !== null) return $cached;

    $result = [
        'country' => 'ar',
        'updatedAt' => date('c'),
        'official' => null,
        'crypto' => null,
        'premium_pct' => null,
        'sourcesUsed' => [],
        'notes' => [],
    ];

    // Official ARS/USD from BCRA (Dólar BNA reference)
    [$body, $code] = curl_get('https://api.bluelytics.com.ar/v2/latest');
    if ($code === 200 && $body) {
        $j = json_decode($body, true);
        if (isset($j['oficial']['value_sell'])) {
            $result['official'] = (float)$j['oficial']['value_sell'];
            $result['sourcesUsed'][] = 'bluelytics.com.ar (BCRA oficial)';
        }
        if (isset($j['blue']['value_sell'])) {
            $result['blue'] = (float)$j['blue']['value_sell'];
        }
    }

    // Crypto ARS/USD — use USDT/ARS market via CoinGecko USD→ARS implied
    [$body2, $code2] = curl_get('https://api.coingecko.com/api/v3/simple/price?ids=tether&vs_currencies=ars');
    if ($code2 === 200 && $body2) {
        $j2 = json_decode($body2, true);
        if (isset($j2['tether']['ars'])) {
            $result['crypto'] = (float)$j2['tether']['ars'];
            $result['sourcesUsed'][] = 'coingecko.com (USDT/ARS spot, unofficial market indicator)';
            $result['notes'][] = 'USDT/ARS rate reflects crypto market, not BCRA official rate.';
        }
    }

    if ($result['official'] && $result['crypto']) {
        $result['premium_pct'] = round(
            (($result['crypto'] - $result['official']) / $result['official']) * 100, 2
        );
    }

    write_cache($cacheFile, $result);
    return $result;
}

// --- Venezuela premium ---
function get_venezuela_premium(): array {
    $cacheFile = CACHE_DIR . 've_premium.json';
    $cached = read_cache($cacheFile, PREMIUM_TTL);
    if ($cached !== null) return $cached;

    $result = [
        'country' => 've',
        'updatedAt' => date('c'),
        'official_bcv' => null,
        'p2p_usdt' => null,
        'premium_pct' => null,
        'sourcesUsed' => [],
        'notes' => ['BCV rate is official. P2P rate reflects actual market. Both are estimates.'],
    ];

    // BCV official USD/VES — try p2p.army or a public aggregator
    [$body, $code] = curl_get('https://pydolarve.org/api/v1/dollar?page=bcv');
    if ($code === 200 && $body) {
        $j = json_decode($body, true);
        if (isset($j['price'])) {
            $result['official_bcv'] = (float)$j['price'];
            $result['sourcesUsed'][] = 'pydolarve.org (BCV rate, unofficial aggregator)';
        }
    }

    // Fallback BCV: static recent value if API fails
    if ($result['official_bcv'] === null) {
        $result['official_bcv'] = 36.5;
        $result['sourcesUsed'][] = 'static fallback (BCV approx. 2026-02)';
        $result['notes'][] = 'BCV rate is a static fallback. Check bcv.org.ve for current rate.';
    }

    // P2P USDT/VES: use CoinGecko USDT implied via USD/VES
    [$body2, $code2] = curl_get('https://pydolarve.org/api/v1/dollar?page=enparalelovzla');
    if ($code2 === 200 && $body2) {
        $j2 = json_decode($body2, true);
        if (isset($j2['price'])) {
            $result['p2p_usdt'] = (float)$j2['price'];
            $result['sourcesUsed'][] = 'pydolarve.org (paralelo/P2P rate, unofficial)';
        }
    }

    // Fallback P2P
    if ($result['p2p_usdt'] === null) {
        $result['p2p_usdt'] = $result['official_bcv'] * 1.12; // approx 12% premium
        $result['notes'][] = 'P2P rate is estimated. Actual P2P varies by platform.';
    }

    if ($result['official_bcv'] && $result['p2p_usdt']) {
        $result['premium_pct'] = round(
            (($result['p2p_usdt'] - $result['official_bcv']) / $result['official_bcv']) * 100, 2
        );
    }

    write_cache($cacheFile, $result);
    return $result;
}

// --- Route ---
$country = strtolower($_GET['country'] ?? 'ar');

if ($country === 'ar') {
    echo json_encode(get_argentina_premium(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} elseif ($country === 've') {
    echo json_encode(get_venezuela_premium(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid country. Use ?country=ar or ?country=ve']);
}
