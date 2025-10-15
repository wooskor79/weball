<?php
// File: src/delete_memo.php
require_once '../core/init.php';

// 로그인 상태 확인
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(403); // Forbidden
    exit('로그인이 필요합니다.');
}

// GET 요청에서 id 파라미터 확인
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400); // Bad Request
    exit('삭제할 메모의 ID가 필요합니다.');
}

$id = $_GET['id'];

// 데이터베이스에서 메모 삭제
$stmt = $conn->prepare("DELETE FROM memos WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // 삭제 성공
    http_response_code(200); // OK
    echo '삭제되었습니다.';
} else {
    // 삭제 실패
    http_response_code(500); // Internal Server Error
    echo '메모 삭제에 실패했습니다.';
}

$stmt->close();
$conn->close();
?>