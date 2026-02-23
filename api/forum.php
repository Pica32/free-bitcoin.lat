<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://free-bitcoin.lat');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

define('DATA_DIR', __DIR__ . '/forum_data/');
define('RATE_LIMIT_DIR', __DIR__ . '/cache/rate_limit/');
define('MAX_BODY_LEN', 2000);
define('MAX_TITLE_LEN', 150);
define('MAX_NICK_LEN', 30);
define('RATE_LIMIT_WINDOW', 300); // 5 minutes
define('RATE_LIMIT_MAX', 5);      // max 5 posts per window

$VALID_CATEGORIES = ['latam','mx','br','ar','ve','co','cl','pe','sv','gt','bo','py','uy','ec','pa','hn'];

function json_ok(mixed $data): void {
    echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

function json_err(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

function sanitize(string $input, int $maxLen): string {
    $s = strip_tags(trim($input));
    return mb_substr($s, 0, $maxLen);
}

function ip_hash(): string {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $ip = explode(',', $ip)[0];
    return hash('sha256', $ip . 'fblt_salt_2026');
}

function check_rate_limit(): bool {
    $dir = RATE_LIMIT_DIR;
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $file = $dir . ip_hash() . '.json';
    $now = time();
    $data = [];
    if (file_exists($file)) {
        $raw = json_decode(file_get_contents($file), true);
        if (is_array($raw)) $data = $raw;
    }
    // Clean old entries
    $data = array_filter($data, fn($ts) => ($now - $ts) < RATE_LIMIT_WINDOW);
    if (count($data) >= RATE_LIMIT_MAX) return false;
    $data[] = $now;
    file_put_contents($file, json_encode(array_values($data)), LOCK_EX);
    return true;
}

function get_threads_file(string $category): string {
    $dir = DATA_DIR . $category . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    return $dir . 'threads.json';
}

function load_threads(string $category): array {
    $file = get_threads_file($category);
    if (!file_exists($file)) return [];
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function save_threads(string $category, array $threads): void {
    $file = get_threads_file($category);
    file_put_contents($file, json_encode($threads, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
}

function generate_id(): string {
    return bin2hex(random_bytes(8));
}

// --- Route ---
$action = strtolower($_GET['action'] ?? '');
$method = $_SERVER['REQUEST_METHOD'];

if ($action === 'list_categories') {
    global $VALID_CATEGORIES;
    $cats = [];
    foreach ($VALID_CATEGORIES as $cat) {
        $threads = load_threads($cat);
        $cats[] = [
            'category' => $cat,
            'threadCount' => count($threads),
            'lastActivity' => !empty($threads) ? max(array_column($threads, 'createdAt')) : null,
        ];
    }
    json_ok($cats);
}

if ($action === 'list_threads') {
    $cat = strtolower($_GET['category'] ?? '');
    global $VALID_CATEGORIES;
    if (!in_array($cat, $VALID_CATEGORIES)) json_err('Invalid category');
    $limit = min(50, max(1, (int)($_GET['limit'] ?? 10)));
    $threads = load_threads($cat);
    usort($threads, fn($a, $b) => strcmp($b['lastActivity'] ?? $b['createdAt'], $a['lastActivity'] ?? $a['createdAt']));
    $threads = array_slice($threads, 0, $limit);
    // Strip replies for list view
    foreach ($threads as &$t) {
        $t['replyCount'] = count($t['replies'] ?? []);
        $t['latestReply'] = !empty($t['replies']) ? end($t['replies'])['createdAt'] : null;
        unset($t['replies']);
    }
    json_ok($threads);
}

if ($action === 'get_thread') {
    $id = $_GET['id'] ?? '';
    $cat = strtolower($_GET['category'] ?? '');
    global $VALID_CATEGORIES;
    if (!in_array($cat, $VALID_CATEGORIES)) json_err('Invalid category');
    $threads = load_threads($cat);
    foreach ($threads as $t) {
        if ($t['id'] === $id) json_ok($t);
    }
    json_err('Thread not found', 404);
}

if ($action === 'create_thread' && $method === 'POST') {
    global $VALID_CATEGORIES;
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $cat = strtolower($input['category'] ?? '');
    if (!in_array($cat, $VALID_CATEGORIES)) json_err('Invalid category');

    $title = sanitize($input['title'] ?? '', MAX_TITLE_LEN);
    $body  = sanitize($input['body'] ?? '', MAX_BODY_LEN);
    $nick  = sanitize($input['nickname'] ?? 'Anónimo', MAX_NICK_LEN);

    // Honeypot
    if (!empty($input['website'])) json_err('Bot detected');

    // Math captcha: client sends answer + expected
    $captchaA = (int)($input['captcha_a'] ?? 0);
    $captchaB = (int)($input['captcha_b'] ?? 0);
    $captchaAnswer = (int)($input['captcha_answer'] ?? -999);
    if ($captchaAnswer !== ($captchaA + $captchaB)) json_err('Captcha incorrecto. Intenta de nuevo.');

    if (strlen($title) < 5) json_err('El título es demasiado corto (mínimo 5 caracteres)');
    if (strlen($body) < 10) json_err('El mensaje es demasiado corto (mínimo 10 caracteres)');

    if (!check_rate_limit()) json_err('Demasiados mensajes recientes. Espera unos minutos.', 429);

    $threads = load_threads($cat);
    $thread = [
        'id' => generate_id(),
        'category' => $cat,
        'title' => $title,
        'body' => $body,
        'nickname' => $nick ?: 'Anónimo',
        'createdAt' => date('c'),
        'lastActivity' => date('c'),
        'replies' => [],
    ];
    array_unshift($threads, $thread);
    // Keep max 500 threads per category
    $threads = array_slice($threads, 0, 500);
    save_threads($cat, $threads);
    json_ok(['id' => $thread['id']]);
}

if ($action === 'create_reply' && $method === 'POST') {
    global $VALID_CATEGORIES;
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $cat = strtolower($input['category'] ?? '');
    $threadId = $input['thread_id'] ?? '';
    if (!in_array($cat, $VALID_CATEGORIES)) json_err('Invalid category');

    $body = sanitize($input['body'] ?? '', MAX_BODY_LEN);
    $nick = sanitize($input['nickname'] ?? 'Anónimo', MAX_NICK_LEN);

    if (!empty($input['website'])) json_err('Bot detected');

    $captchaA = (int)($input['captcha_a'] ?? 0);
    $captchaB = (int)($input['captcha_b'] ?? 0);
    $captchaAnswer = (int)($input['captcha_answer'] ?? -999);
    if ($captchaAnswer !== ($captchaA + $captchaB)) json_err('Captcha incorrecto.');

    if (strlen($body) < 5) json_err('El mensaje es demasiado corto');
    if (!check_rate_limit()) json_err('Demasiados mensajes recientes.', 429);

    $threads = load_threads($cat);
    $found = false;
    foreach ($threads as &$t) {
        if ($t['id'] === $threadId) {
            if (!isset($t['replies'])) $t['replies'] = [];
            // Max 200 replies per thread
            if (count($t['replies']) >= 200) json_err('Este hilo ha alcanzado el límite de respuestas.');
            $t['replies'][] = [
                'id' => generate_id(),
                'body' => $body,
                'nickname' => $nick ?: 'Anónimo',
                'createdAt' => date('c'),
            ];
            $t['lastActivity'] = date('c');
            $found = true;
            break;
        }
    }
    unset($t);
    if (!$found) json_err('Hilo no encontrado', 404);
    save_threads($cat, $threads);
    json_ok(['ok' => true]);
}

json_err('Acción desconocida o método no permitido', 400);
