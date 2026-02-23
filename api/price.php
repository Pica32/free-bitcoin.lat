<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://free-bitcoin.lat');
header('Cache-Control: public, max-age=300');

define('CACHE_DIR', __DIR__ . '/cache/');
define('BTC_CACHE_FILE', CACHE_DIR . 'btc_price.json');
define('FX_CACHE_FILE', CACHE_DIR . 'fx_rates.json');
define('BTC_TTL', 300);   // 5 minutes
define('FX_TTL', 3600);   // 60 minutes

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
    $age = time() - filemtime($file);
    if ($age > $ttl) return null;
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : null;
}

function write_cache(string $file, array $data): void {
    if (!is_dir(CACHE_DIR)) {
        mkdir(CACHE_DIR, 0755, true);
    }
    file_put_contents($file, json_encode($data), LOCK_EX);
}

// --- BTC/USD price from Binance ---
function fetch_btc_usd(): ?array {
    $cached = read_cache(BTC_CACHE_FILE, BTC_TTL);
    if ($cached !== null) return $cached;

    [$body, $code] = curl_get('https://api.binance.com/api/v3/ticker/24hr?symbol=BTCUSDT');
    if ($code === 200 && $body) {
        $j = json_decode($body, true);
        if (isset($j['lastPrice'])) {
            $data = [
                'price' => (float)$j['lastPrice'],
                'change24h' => (float)($j['priceChangePercent'] ?? 0),
                'high24h' => (float)($j['highPrice'] ?? 0),
                'low24h'  => (float)($j['lowPrice'] ?? 0),
                'source'  => 'binance',
            ];
            write_cache(BTC_CACHE_FILE, $data);
            return $data;
        }
    }

    // Fallback: CoinGecko
    [$body2, $code2] = curl_get('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd&include_24hr_change=true');
    if ($code2 === 200 && $body2) {
        $j2 = json_decode($body2, true);
        if (isset($j2['bitcoin']['usd'])) {
            $data = [
                'price' => (float)$j2['bitcoin']['usd'],
                'change24h' => (float)($j2['bitcoin']['usd_24h_change'] ?? 0),
                'high24h' => 0,
                'low24h'  => 0,
                'source'  => 'coingecko',
            ];
            write_cache(BTC_CACHE_FILE, $data);
            return $data;
        }
    }

    return null;
}

// --- FX rates (USD → local currencies) ---
function fetch_fx_rates(): array {
    $cached = read_cache(FX_CACHE_FILE, FX_TTL);
    if ($cached !== null) return $cached;

    $currencies = 'MXN,BRL,ARS,COP,CLP,PEN,GTQ,BOB,PYG,UYU,HNL,PAB';

    // Primary: exchangerate-api (free tier)
    [$body, $code] = curl_get("https://open.er-api.com/v6/latest/USD");
    if ($code === 200 && $body) {
        $j = json_decode($body, true);
        if (isset($j['rates'])) {
            $rates = $j['rates'];
            $data = [
                'MXN' => (float)($rates['MXN'] ?? 17.5),
                'BRL' => (float)($rates['BRL'] ?? 5.0),
                'ARS' => (float)($rates['ARS'] ?? 1050),
                'COP' => (float)($rates['COP'] ?? 4100),
                'CLP' => (float)($rates['CLP'] ?? 930),
                'PEN' => (float)($rates['PEN'] ?? 3.7),
                'GTQ' => (float)($rates['GTQ'] ?? 7.8),
                'BOB' => (float)($rates['BOB'] ?? 6.9),
                'PYG' => (float)($rates['PYG'] ?? 7700),
                'UYU' => (float)($rates['UYU'] ?? 40),
                'HNL' => (float)($rates['HNL'] ?? 25),
                'PAB' => 1.0, // Panamanian Balboa = 1 USD
                'VES' => 36.5, // Venezuelan Bolivar — approximate, updated manually
                'USD' => 1.0,
                'SVC' => 8.75, // El Salvador Colon (pegged) / uses USD effectively
                'source' => 'open.er-api.com',
                'cachedAt' => time(),
            ];
            write_cache(FX_CACHE_FILE, $data);
            return $data;
        }
    }

    // Fallback: static approximate rates (updated 2026-02-23)
    return [
        'MXN' => 17.5, 'BRL' => 5.7, 'ARS' => 1050, 'VES' => 36.5,
        'COP' => 4100, 'CLP' => 930, 'PEN' => 3.7, 'SVC' => 8.75,
        'GTQ' => 7.8, 'BOB' => 6.9, 'PYG' => 7700, 'UYU' => 40,
        'PAB' => 1.0, 'HNL' => 25, 'USD' => 1.0,
        'source' => 'static_fallback', 'cachedAt' => time(),
    ];
}

// --- Main logic ---
$btc = fetch_btc_usd();
if ($btc === null) {
    http_response_code(503);
    echo json_encode(['error' => 'BTC price unavailable', 'updatedAt' => date('c')]);
    exit;
}

$fx = fetch_fx_rates();
$btcUsd = $btc['price'];

$currencies = ['USD', 'MXN', 'BRL', 'ARS', 'VES', 'COP', 'CLP', 'PEN', 'SVC', 'GTQ', 'BOB', 'PYG', 'UYU', 'PAB', 'HNL'];
$prices = [];
foreach ($currencies as $cur) {
    $rate = (float)($fx[$cur] ?? 1.0);
    $prices[$cur] = [
        'price'    => round($btcUsd * $rate, $rate > 100 ? 0 : 2),
        'currency' => $cur,
        'fxRate'   => $rate,
    ];
}

echo json_encode([
    'updatedAt' => date('c'),
    'base'      => 'BTC',
    'btcUsd'    => $btcUsd,
    'fxSource'  => $fx['source'] ?? 'unknown',
    'btcSource' => $btc['source'],
    'prices'    => $prices,
    'change24h' => ['USD' => round($btc['change24h'], 2)],
    'high24h'   => $btc['high24h'],
    'low24h'    => $btc['low24h'],
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
