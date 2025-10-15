<?php
// public/index.php (수정됨)
require_once __DIR__ . '/../app/bootstrap.php';

// 로그인 상태에 따라 적절한 페이지로 리디렉션합니다.
if (is_logged_in()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit();