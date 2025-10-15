<?php
require_once __DIR__ . '/functions.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Use POST']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $longUrl = sanitize_url($input['url'] ?? '');
    $custom  = trim($input['alias'] ?? ''); // title 역할
    $expireDays = isset($input['expire_days']) ? (int)$input['expire_days'] : DEFAULT_EXPIRE_DAYS;

    if (!is_valid_url($longUrl)) {
        throw new Exception('Invalid URL');
    }

    // [수정됨] 로직 단순화: 항상 새 링크 생성
    $row = create_new_link($longUrl, $expireDays, $custom);

    echo json_encode([
        'short' => short_url($row['code']),
        'code'  => $row['code'],
        'title' => $row['title'] ?? null,
        'long'  => $row['long_url'],
        'expires_at' => $row['expires_at'],
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}