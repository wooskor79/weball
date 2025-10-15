<?php
// public/logout.php (수정됨)

require_once __DIR__ . '/../app/bootstrap.php';

logout();

// 로그아웃 후, 부모 창을 로그인 페이지로 보냅니다.
echo "<script>parent.location.href = 'login.php';</script>";
exit();

