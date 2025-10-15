<?php
// File: src/init.php
// 데이터베이스 연결 및 모든 데이터 조회 로직 담당
session_start();
require_once 'db.php';

// 그룹 목록 가져오기
$groups_result = $conn->query("
    SELECT * FROM groups
    ORDER BY
        CASE
            WHEN name = '기본 그룹' THEN 1
            ELSE 2
        END,
        name ASC
");
$groups = [];
while($row = $groups_result->fetch_assoc()) {
    $groups[] = $row;
}

// 즐겨찾기 목록 그룹별로 묶기
$favorites_by_group = [];
$fav_query = "
    SELECT g.id as group_id, g.name as group_name, f.id, f.url, f.alias
    FROM favorites f
    LEFT JOIN groups g ON f.group_id = g.id
    ORDER BY
        CASE
            WHEN g.name = '기본 그룹' THEN 1
            ELSE 2
        END,
        g.name ASC,
        f.created_at DESC";
$fav_result = $conn->query($fav_query);
while($row = $fav_result->fetch_assoc()){
    $group_name = $row['group_name'] ?? '미분류';
    if(!empty($group_name)) {
        $favorites_by_group[$group_name][] = $row;
    }
}

// 즐겨찾기 없는 그룹 제거 (기본 그룹 제외)
foreach ($groups as $key => $group) {
    if (!isset($favorites_by_group[$group['name']])) {
        if ($group['name'] !== '기본 그룹') {
            unset($groups[$key]);
        }
    }
}

// 메모 목록 불러오기
$memos = [];
$memo_query = "SELECT id, title, content, created_at FROM memos ORDER BY created_at DESC";
if ($memo_result = $conn->query($memo_query)) {
    while($row = $memo_result->fetch_assoc()) {
        $memos[] = $row;
    }
}

// 빠른 링크 목록 불러오기
$quick_links = [];
$quick_links_query = "SELECT * FROM quick_links ORDER BY created_at ASC";
if ($quick_links_result = $conn->query($quick_links_query)) {
    while($row = $quick_links_result->fetch_assoc()) {
        $quick_links[] = $row;
    }
}

// 세션 오류 메시지 처리
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

// 로그인 상태 확인
$is_loggedin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

?>