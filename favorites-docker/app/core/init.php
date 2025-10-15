<?php
// File: app/core/init.php

// =============== 1. 기본 설정 ===============
session_start();


// =============== 2. 데이터베이스 연결 ===============
$db_host = 'common_database_server';
$db_name = 'favorites_db';
$db_user = 'root';
$db_pass = 'dldntjd@D79';

$conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("데이터베이스 연결 실패: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");


// =============== 3. 데이터 조회 ===============

// 그룹 목록 (즐겨찾기에서 그룹 기능이 제거되어 더 이상 필요하지 않음)
$groups = [];
$groups_result = $conn->query("SELECT * FROM groups ORDER BY CASE WHEN name = '기본 그룹' THEN 1 ELSE 2 END, name ASC");
if ($groups_result) {
    while($row = $groups_result->fetch_assoc()) {
        $groups[] = $row;
    }
}

// 즐겨찾기 목록 (그룹 없이 전체 목록을 가져오도록 수정)
$favorites = [];
$fav_result = $conn->query("SELECT * FROM favorites ORDER BY created_at DESC");
if ($fav_result) {
    while($row = $fav_result->fetch_assoc()) {
        $favorites[] = $row;
    }
}
// 이전 그룹별 즐겨찾기 로직은 삭제되었습니다.

// 메모 목록
$memos = [];
$memo_result = $conn->query("SELECT id, title, content, created_at FROM memos ORDER BY created_at DESC");
if ($memo_result) {
    while($row = $memo_result->fetch_assoc()) {
        $memos[] = $row;
    }
}

// 빠른 링크 목록
$quick_links = [];
$quick_links_result = $conn->query("SELECT * FROM quick_links ORDER BY created_at ASC");
if ($quick_links_result) {
    while($row = $quick_links_result->fetch_assoc()) {
        $quick_links[] = $row;
    }
}

// =============== 4. 변수 설정 ===============
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

$is_loggedin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

?>