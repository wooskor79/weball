<?php
// ========== 문제 해결을 위한 오류 표시 코드 (나중에 삭제 가능) ==========
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ===============================================================

require_once __DIR__ . '/functions.php';

$code = $_GET['code'] ?? '';
$code = trim($code, "/ \t\n\r\0\x0B");
$code = normalize_code($code);

if ($code === '' || !is_valid_code($code) || is_reserved_code($code)) {
    http_response_code(404);
    echo "Error: Code is invalid or not found.";
    exit;
}

$row = find_by_code($code);
if (!$row) {
    http_response_code(404);
    echo "Error: Link data could not be found for the code '{$code}'.";
    exit;
}

// 만료 체크
if (!empty($row['expires_at'])) {
    $now = new DateTime('now', new DateTimeZone('UTC'));
    $exp = new DateTime($row['expires_at'], new DateTimeZone('UTC'));
    if ($now > $exp) {
        http_response_code(410); // Gone
        echo "This link has expired.";
        exit;
    }
}

// 클릭 카운트 증가
increment_click($code, (int)$row['id']);

// 설정된 상태 코드(REDIRECT_STATUS)로 리다이렉트
$status = (int)REDIRECT_STATUS;
if (!in_array($status, [301,302], true)) $status = 302;

// header() 함수 호출 전에 어떤 출력도 없어야 합니다.
header('Location: ' . $row['long_url'], true, $status);
exit;

// 마지막에 있던 불필요한 중괄호를 제거했습니다.