<?php
require_once __DIR__ . '/config.php';

/** ========= 유틸 ========= */

// ... (base62_alphabet, base62_encode 등 다른 함수들은 변경 없음) ...
function base62_alphabet(): string {
    return '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
}

function base62_encode(int $num): string {
    $alphabet = base62_alphabet();
    $base = strlen($alphabet);
    if ($num === 0) return $alphabet[0];
    $str = '';
    while ($num > 0) {
        $str = $alphabet[$num % $base] . $str;
        $num = intdiv($num, $base);
    }
    return $str;
}

function normalize_code(string $code): string {
    if (class_exists('Normalizer')) {
        $n = Normalizer::normalize($code, Normalizer::FORM_C);
        if ($n !== false) return $n;
    }
    return $code;
}

function sanitize_url(string $url): string {
    $url = trim($url);
    if ($url === '') return '';
    if (!preg_match('~^https?://~i', $url)) {
        $url = 'http://' . $url;
    }
    return $url;
}

function is_valid_url(string $url): bool {
    if (strlen($url) > MAX_URL_LENGTH) return false;
    return (bool) filter_var($url, FILTER_VALIDATE_URL);
}

function is_valid_code(string $code): bool {
    return (bool) preg_match('/^[a-zA-Z0-9\-_]{3,64}$/', $code);
}

function contains_hangul(string $str): bool {
    return (bool) preg_match('/[ㄱ-ㅎ|ㅏ-ㅣ|가-힣]/u', $str);
}

function is_reserved_code(string $code): bool {
    global $RESERVED_CODES;
    return in_array(strtolower(normalize_code($code)), array_map(
        fn($x)=>strtolower($x), $RESERVED_CODES
    ), true);
}

function now_utc(): string {
    return (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
}

function ip_address(): string {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function user_agent(): string {
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
}


/** ========= DB ========= */

function pdo(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}

function short_url(string $code): string {
    $code = normalize_code($code);
    return BASE_URL . '/' . rawurlencode($code);
}

/** ========= CRUD ========= */

function find_by_long_url(string $longUrl) {
    $stmt = pdo()->prepare('
        SELECT * FROM links 
        WHERE long_url = :u 
          AND (expires_at IS NULL OR expires_at > UTC_TIMESTAMP())
          AND code IS NOT NULL
        ORDER BY id ASC 
        LIMIT 1
    ');
    $stmt->execute([':u' => $longUrl]);
    return $stmt->fetch();
}

function find_by_code(string $code) {
    $code = normalize_code($code);
    $stmt = pdo()->prepare('SELECT * FROM links WHERE code = :c LIMIT 1');
    $stmt->execute([':c' => $code]);
    return $stmt->fetch();
}

/**
 * [수정됨] 함수 이름을 create_new_link로 변경하고, 항상 5자리 영문 코드를 생성하도록 로직 변경
 * 기존 create_with_custom_alias 함수는 삭제됨.
 */
function create_new_link(string $longUrl, ?int $expireDays, string $title) {
    $pdo = pdo();
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';

    // 중복되지 않는 5자리 코드를 찾을 때까지 반복
    do {
        $code = '';
        for ($i = 0; $i < 5; $i++) { // 길이를 5로 고정
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        $stmt = $pdo->prepare('SELECT 1 FROM links WHERE code = :c LIMIT 1');
        $stmt->execute([':c' => $code]);
    } while ($stmt->fetchColumn() || is_reserved_code($code));

    // 고유 코드 생성 후 DB에 삽입
    $expiresAt = null;
    if (!empty($expireDays) && $expireDays > 0) {
        $d = new DateTime('now', new DateTimeZone('UTC'));
        $d->modify('+' . (int)$expireDays . ' days');
        $expiresAt = $d->format('Y-m-d H:i:s');
    }

    $stmt = pdo()->prepare(
        'INSERT INTO links (long_url, code, title, created_at, expires_at, creator_ip, user_agent) 
         VALUES (:u, :c, :t, UTC_TIMESTAMP(), :e, :ip, :ua)'
    );
    $stmt->execute([
        ':u' => $longUrl,
        ':c' => $code,
        ':t' => $title ?: null,
        ':e' => $expiresAt,
        ':ip' => ip_address(),
        ':ua' => user_agent(),
    ]);

    return find_by_code($code);
}

function increment_click(string $code, int $linkId) {
    $stmt = pdo()->prepare('
        UPDATE links 
        SET click_count = click_count + 1, last_clicked = UTC_TIMESTAMP() 
        WHERE id = :id
    ');
    $stmt->execute([':id' => $linkId]);
}

function count_links(?string $search): int {
    $sql = 'SELECT COUNT(*) AS c FROM links WHERE code IS NOT NULL';
    $params = [];
    if (!empty($search)) {
        $sql .= ' AND (code LIKE :q OR title LIKE :q)';
        $params[':q'] = '%' . $search . '%';
    }
    $stmt = pdo()->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch();
    return (int)($row['c'] ?? 0);
}

function list_links(?string $search, int $limit = 20, int $offset = 0): array {
    $sql = 'SELECT * FROM links WHERE code IS NOT NULL';
    $params = [];
    if (!empty($search)) {
        $sql .= ' AND (code LIKE :q OR title LIKE :q)';
        $params[':q'] = '%' . $search . '%';
    }
    $sql .= ' ORDER BY id DESC LIMIT :lim OFFSET :off';
    $stmt = pdo()->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v, PDO::PARAM_STR);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function delete_link(int $id): bool {
    $stmt = pdo()->prepare('DELETE FROM links WHERE id = :id');
    return $stmt->execute([':id' => $id]);
}

function update_expiry_days(int $id, int $days): bool {
    if ($days <= 0) {
        $stmt = pdo()->prepare('UPDATE links SET expires_at = NULL WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
    $d = new DateTime('now', new DateTimeZone('UTC'));
    $d->modify('+' . $days . ' days');
    $expiresAt = $d->format('Y-m-d H:i:s');

    $stmt = pdo()->prepare('UPDATE links SET expires_at = :e WHERE id = :id');
    return $stmt->execute([':e' => $expiresAt, ':id' => $id]);
}

/**
 * [새로 추가됨] 별칭(title)을 업데이트하는 함수
 */
function update_alias(int $id, string $title): bool {
    $stmt = pdo()->prepare('UPDATE links SET title = :t WHERE id = :id');
    return $stmt->execute([':t' => $title ?: null, ':id' => $id]);
}